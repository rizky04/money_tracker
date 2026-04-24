<?php

namespace App\Ai\Agents;

use App\Models\Expense;
use App\Models\User;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;
use Laravel\Ai\Enums\Lab;

#[Provider(Lab::Gemini)]
#[Temperature(0.7)]
class ExpenseAssistant implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function __construct(protected User $user) {}

    public function instructions(): string
    {
        $summary = $this->getExpenseSummary();

        return <<<INSTRUCTIONS
Anda adalah asisten virtual untuk aplikasi pencatatan pengeluaran belanja (expense tracker).

RINGKASAN DATA PENGELUARAN USER:
{$summary}

INFORMASI YANG TERSEDIA:
- Struk belanja (tanggal, nama toko/merchant, total belanja)
- Rincian barang per struk (nama barang, qty, harga satuan, subtotal)
- Semua angka dalam Rupiah (IDR)

CARA MENJAWAB:
1. Gunakan data ringkasan di atas untuk menjawab pertanyaan user
2. Jawab dengan bahasa Indonesia yang ramah, singkat, dan informatif
3. Format angka pakai pemisah ribuan titik (contoh: Rp 1.250.000)
4. Kalau ditanya rekomendasi hemat, kasih saran praktis berdasarkan pola belanja user
5. Jangan menjawab di luar topik pengeluaran/belanja — arahkan kembali ke scope expense tracker

Jika user belum punya data, sarankan scan struk atau input manual di halaman Catat.
INSTRUCTIONS;
    }

    private function getExpenseSummary(): string
    {
        $query = Expense::where('user_id', $this->user->id);
        $count = (clone $query)->count();

        if ($count === 0) {
            return 'Belum ada data pengeluaran.';
        }

        $now = \Carbon\Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth()->toDateString();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth()->toDateString();

        $totalAllTime = (clone $query)->sum('total_amount');
        $totalThisMonth = (clone $query)->whereBetween('date', [$startOfMonth, $endOfMonth])->sum('total_amount');
        $countThisMonth = (clone $query)->whereBetween('date', [$startOfMonth, $endOfMonth])->count();
        $totalLastMonth = (clone $query)->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])->sum('total_amount');

        $topMerchants = (clone $query)
            ->selectRaw('merchant, SUM(total_amount) as total, COUNT(*) as cnt')
            ->whereNotNull('merchant')
            ->where('merchant', '!=', '')
            ->groupBy('merchant')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $recent = (clone $query)
            ->with('items')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $s = "- Total semua pengeluaran: Rp " . number_format($totalAllTime, 0, ',', '.') . " ({$count} struk)\n";
        $s .= "- Bulan ini ({$now->format('F Y')}): Rp " . number_format($totalThisMonth, 0, ',', '.') . " ({$countThisMonth} struk)\n";
        $s .= "- Bulan lalu: Rp " . number_format($totalLastMonth, 0, ',', '.') . "\n";

        if ($totalLastMonth > 0) {
            $diff = (($totalThisMonth - $totalLastMonth) / $totalLastMonth) * 100;
            $arrow = $diff > 0 ? 'naik' : ($diff < 0 ? 'turun' : 'sama');
            $s .= "- Perubahan vs bulan lalu: {$arrow} " . number_format(abs($diff), 1) . "%\n";
        }

        if ($topMerchants->isNotEmpty()) {
            $s .= "\nTOP 5 MERCHANT (total belanja terbanyak):\n";
            foreach ($topMerchants as $m) {
                $s .= "• {$m->merchant}: Rp " . number_format($m->total, 0, ',', '.') . " ({$m->cnt}x)\n";
            }
        }

        if ($recent->isNotEmpty()) {
            $s .= "\n5 STRUK TERAKHIR:\n";
            foreach ($recent as $e) {
                $date = \Carbon\Carbon::parse($e->date)->format('d M Y');
                $merchant = $e->merchant ?: '(tanpa nama)';
                $itemCount = $e->items->count();
                $s .= "• {$date} — {$merchant}: Rp " . number_format($e->total_amount, 0, ',', '.') . " ({$itemCount} barang)\n";
            }
        }

        return $s;
    }
}
