<?php

namespace App\Ai\Agents;

use App\Models\FuelEntry;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Enums\Lab;
use Illuminate\Support\Facades\DB;

#[Provider(Lab::Gemini)]
#[Temperature(0.7)] // Ideal untuk chatbot asisten
class FuelAssistant implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function __construct(
        protected User $user,
        protected ?Vehicle $vehicle = null
    ) {}

    public function instructions(): string
    {
        $vehicleInfo = $this->vehicle
            ? "User saat ini menggunakan kendaraan: {$this->vehicle->name} ({$this->vehicle->license_plate}) dengan odometer awal: " . number_format($this->vehicle->odometer_initial, 0, ',', '.') . " KM"
            : "User belum memiliki kendaraan terdaftar.";

        // Ambil data ringkasan untuk konteks
        $summary = $this->getFuelSummary();

        return <<<INSTRUCTIONS
Anda adalah asisten virtual untuk aplikasi pencatatan BBM (bahan bakar) kendaraan.

DATA KONTEKS:
{$vehicleInfo}

RINGKASAN DATA BBM:
{$summary}

INFORMASI YANG TERSEDIA:
- Data pengisian BBM (tanggal, jenis BBM, liter, harga, total harga, odometer)
- Data kendaraan (nama, plat nomor, odometer awal)
- Efisiensi BBM (KM/Liter) sudah dihitung otomatis

ANALISIS YANG TERSEDIA:
- Rata-rata konsumsi BBM per kendaraan
- Total pengeluaran per periode
- Perbandingan efisiensi antar kendaraan
- Tren harga BBM

CARA MENJAWAB:
1. Gunakan data dari ringkasan di atas untuk menjawab pertanyaan umum
2. Untuk pertanyaan spesifik, Anda bisa melakukan query ke database melalui kode tools
3. Jawab dengan bahasa Indonesia yang ramah dan informatif
4. Berikan saran praktis untuk penghematan BBM

Jika data tidak tersedia, sarankan user untuk mengisi data BBM terlebih dahulu.
INSTRUCTIONS;
    }

    private function getFuelSummary(): string
    {
        $query = FuelEntry::where('user_id', $this->user->id);

        if ($this->vehicle) {
            $query->where('vehicle_id', $this->vehicle->id);
        }

        $totalSpent = $query->sum('total_price');
        $totalLiters = $query->sum('liters');
        $totalDistance = 0;

        // Hitung total jarak
        $entries = $query->orderBy('odometer')->get();
        if ($entries->count() > 1) {
            $totalDistance = $entries->last()->odometer - $entries->first()->odometer;
        }

        $avgEfficiency = $query->avg('fuel_efficiency');
        $lastEntry = $query->latest('date')->first();

        $summary = "Total pengeluaran: Rp " . number_format($totalSpent, 0, ',', '.') . "\n";
        $summary .= "Total liter: " . number_format($totalLiters, 1, ',', '.') . " L\n";
        $summary .= "Total jarak: " . number_format($totalDistance, 0, ',', '.') . " KM\n";

        if ($avgEfficiency) {
            $summary .= "Rata-rata efisiensi: " . number_format($avgEfficiency, 1, ',', '.') . " KM/L\n";
        }

        if ($lastEntry) {
            $summary .= "Pengisian terakhir: " . $lastEntry->date->format('d M Y') . " - {$lastEntry->fuel_type}\n";
        }

        return $summary;
    }

    // Method helper untuk query data (bisa dipanggil agent)
    public function getFuelStats($period = 'month')
    {
        $query = FuelEntry::where('user_id', $this->user->id);

        if ($this->vehicle) {
            $query->where('vehicle_id', $this->vehicle->id);
        }

        switch ($period) {
            case 'week':
                $query->where('date', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('date', '>=', now()->subMonth());
                break;
            case 'year':
                $query->where('date', '>=', now()->subYear());
                break;
        }

        return [
            'total_cost' => $query->sum('total_price'),
            'total_liters' => $query->sum('liters'),
            'avg_efficiency' => $query->avg('fuel_efficiency'),
            'entries_count' => $query->count(),
            'by_fuel_type' => $query->select('fuel_type', DB::raw('SUM(liters) as total_liters, SUM(total_price) as total_cost'))
                ->groupBy('fuel_type')
                ->get()
        ];
    }
}
