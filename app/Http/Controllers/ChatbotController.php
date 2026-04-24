<?php

namespace App\Http\Controllers;

use App\Ai\Agents\ExpenseAssistant;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot.index');
    }

    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = Auth::user();

        try {
            $stats = $this->getExpenseStatistics($user->id);
            $contextPrompt = $this->buildContextPrompt($request->message, $stats);

            $agent = new ExpenseAssistant($user);

            if ($request->conversation_id) {
                $agent = $agent->continue($request->conversation_id, as: $user);
            }

            $response = $agent->prompt($contextPrompt);

            return response()->json([
                'success' => true,
                'message' => (string) $response,
                'conversation_id' => $response->conversationId,
            ]);

        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Maaf, terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getExpenseStatistics(int $userId): array
    {
        try {
            $expenses = Expense::with('items')
                ->where('user_id', $userId)
                ->orderBy('date', 'asc')
                ->get();

            if ($expenses->isEmpty()) {
                return [
                    'has_data' => false,
                    'message' => 'Belum ada data pengeluaran.',
                ];
            }

            $now = \Carbon\Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth()->toDateString();
            $endOfMonth = $now->copy()->endOfMonth()->toDateString();
            $startOfLastMonth = $now->copy()->subMonth()->startOfMonth()->toDateString();
            $endOfLastMonth = $now->copy()->subMonth()->endOfMonth()->toDateString();

            $totalAllTime = $expenses->sum('total_amount');
            $countAllTime = $expenses->count();
            $first = $expenses->first();
            $last = $expenses->last();

            $thisMonth = $expenses->filter(fn($e) => $e->date >= $startOfMonth && $e->date <= $endOfMonth);
            $lastMonth = $expenses->filter(fn($e) => $e->date >= $startOfLastMonth && $e->date <= $endOfLastMonth);

            // Merchant stats
            $merchantStats = [];
            foreach ($expenses as $e) {
                $m = trim((string) $e->merchant) ?: '(tanpa nama)';
                if (!isset($merchantStats[$m])) {
                    $merchantStats[$m] = ['merchant' => $m, 'total' => 0, 'count' => 0];
                }
                $merchantStats[$m]['total'] += $e->total_amount;
                $merchantStats[$m]['count']++;
            }
            usort($merchantStats, fn($a, $b) => $b['total'] <=> $a['total']);
            $topMerchants = array_slice($merchantStats, 0, 5);

            // Monthly trend (6 bulan terakhir)
            $monthlyData = [];
            foreach ($expenses as $e) {
                $d = \Carbon\Carbon::parse($e->date);
                $key = $d->format('Y-m');
                $label = $d->format('F Y');
                if (!isset($monthlyData[$key])) {
                    $monthlyData[$key] = ['month' => $label, 'total' => 0, 'count' => 0];
                }
                $monthlyData[$key]['total'] += $e->total_amount;
                $monthlyData[$key]['count']++;
            }
            ksort($monthlyData);
            $monthlyStats = array_values(array_slice($monthlyData, -6));

            // Top item (barang termahal dan paling sering dibeli)
            $itemStats = [];
            foreach ($expenses as $e) {
                foreach ($e->items as $it) {
                    $name = strtolower(trim($it->name));
                    if ($name === '') continue;
                    if (!isset($itemStats[$name])) {
                        $itemStats[$name] = ['name' => $it->name, 'qty' => 0, 'total' => 0, 'count' => 0];
                    }
                    $itemStats[$name]['qty'] += $it->qty;
                    $itemStats[$name]['total'] += $it->subtotal;
                    $itemStats[$name]['count']++;
                }
            }
            usort($itemStats, fn($a, $b) => $b['total'] <=> $a['total']);
            $topItems = array_slice($itemStats, 0, 5);

            return [
                'has_data' => true,
                'total_all_time' => $totalAllTime,
                'count_all_time' => $countAllTime,
                'avg_per_struk' => $countAllTime > 0 ? $totalAllTime / $countAllTime : 0,
                'total_this_month' => $thisMonth->sum('total_amount'),
                'count_this_month' => $thisMonth->count(),
                'total_last_month' => $lastMonth->sum('total_amount'),
                'count_last_month' => $lastMonth->count(),
                'first_date' => \Carbon\Carbon::parse($first->date)->format('d M Y'),
                'last_date' => \Carbon\Carbon::parse($last->date)->format('d M Y'),
                'top_merchants' => $topMerchants,
                'monthly_stats' => $monthlyStats,
                'top_items' => $topItems,
                'recent_expenses' => $expenses->sortByDesc('date')->take(5)->map(function ($e) {
                    return [
                        'date' => \Carbon\Carbon::parse($e->date)->format('d M Y'),
                        'merchant' => $e->merchant ?: '(tanpa nama)',
                        'total' => $e->total_amount,
                        'item_count' => $e->items->count(),
                    ];
                })->values()->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting expense statistics: ' . $e->getMessage());
            return [
                'has_data' => false,
                'message' => 'Error memuat data: ' . $e->getMessage(),
            ];
        }
    }

    private function buildContextPrompt(string $userQuestion, array $stats): string
    {
        $ctx = "Pertanyaan user: {$userQuestion}\n\n";
        $ctx .= "DATA PENGELUARAN USER:\n";

        if (empty($stats['has_data'])) {
            $ctx .= "Belum ada data pengeluaran. Sarankan user scan struk atau input manual via menu Catat.\n";
            $ctx .= "Jawab dengan bahasa Indonesia yang ramah.\n";
            return $ctx;
        }

        $ctx .= "💰 RINGKASAN UMUM:\n";
        $ctx .= "- Total pengeluaran: Rp " . number_format($stats['total_all_time'], 0, ',', '.') . "\n";
        $ctx .= "- Jumlah struk: {$stats['count_all_time']}\n";
        $ctx .= "- Rata-rata per struk: Rp " . number_format($stats['avg_per_struk'], 0, ',', '.') . "\n";
        $ctx .= "- Periode data: {$stats['first_date']} - {$stats['last_date']}\n\n";

        $ctx .= "📅 BULAN INI vs BULAN LALU:\n";
        $ctx .= "- Bulan ini: Rp " . number_format($stats['total_this_month'], 0, ',', '.') . " ({$stats['count_this_month']} struk)\n";
        $ctx .= "- Bulan lalu: Rp " . number_format($stats['total_last_month'], 0, ',', '.') . " ({$stats['count_last_month']} struk)\n";
        if ($stats['total_last_month'] > 0) {
            $diff = (($stats['total_this_month'] - $stats['total_last_month']) / $stats['total_last_month']) * 100;
            $arrow = $diff > 0 ? '↑ naik' : ($diff < 0 ? '↓ turun' : 'sama');
            $ctx .= "- Perubahan: {$arrow} " . number_format(abs($diff), 1) . "%\n";
        }
        $ctx .= "\n";

        if (!empty($stats['top_merchants'])) {
            $ctx .= "🏪 TOP MERCHANT (pengeluaran terbanyak):\n";
            foreach ($stats['top_merchants'] as $m) {
                $ctx .= "• {$m['merchant']}: Rp " . number_format($m['total'], 0, ',', '.') . " ({$m['count']}x kunjungan)\n";
            }
            $ctx .= "\n";
        }

        if (!empty($stats['top_items'])) {
            $ctx .= "🛒 TOP BARANG (total belanja terbesar):\n";
            foreach ($stats['top_items'] as $it) {
                $ctx .= "• {$it['name']}: Rp " . number_format($it['total'], 0, ',', '.') . " (qty total: {$it['qty']}, {$it['count']}x beli)\n";
            }
            $ctx .= "\n";
        }

        if (!empty($stats['monthly_stats'])) {
            $ctx .= "📈 TREN BULANAN (6 bulan terakhir):\n";
            foreach ($stats['monthly_stats'] as $m) {
                $avg = $m['count'] > 0 ? $m['total'] / $m['count'] : 0;
                $ctx .= "• {$m['month']}: Rp " . number_format($m['total'], 0, ',', '.') . " ({$m['count']} struk, rata-rata Rp " . number_format($avg, 0, ',', '.') . "/struk)\n";
            }
            $ctx .= "\n";
        }

        if (!empty($stats['recent_expenses'])) {
            $ctx .= "🕒 STRUK TERBARU:\n";
            foreach ($stats['recent_expenses'] as $e) {
                $ctx .= "• {$e['date']} — {$e['merchant']}: Rp " . number_format($e['total'], 0, ',', '.') . " ({$e['item_count']} barang)\n";
            }
            $ctx .= "\n";
        }

        $ctx .= "INSTRUKSI:\n";
        $ctx .= "1. Jawab dengan bahasa Indonesia yang ramah dan singkat\n";
        $ctx .= "2. Gunakan data di atas sebagai sumber jawaban\n";
        $ctx .= "3. Format angka pakai pemisah ribuan titik (contoh: Rp 1.250.000)\n";
        $ctx .= "4. Kalau diminta rekomendasi, kasih saran hemat berdasarkan pola belanja user\n";
        $ctx .= "5. Kalau ditanya di luar topik pengeluaran, arahkan balik ke topik expense tracker\n";
        $ctx .= "6. Kalau data yang dibutuhkan tidak ada di ringkasan, sampaikan apa adanya\n";

        return $ctx;
    }
}
