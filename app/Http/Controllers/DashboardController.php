<?php

namespace App\Http\Controllers;

use App\Models\FuelEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    private function getActiveVehicle()
    {
        $user = Auth::user();

        // Cari kendaraan yang is_active-nya true
        $activeVehicle = $user->vehicles()->where('is_active', true)->first();

        // Kalau tidak ada yang aktif (misal baru pertama kali tambah), ambil yang terbaru saja
        if (!$activeVehicle) {
            $activeVehicle = $user->vehicles()->latest()->first();
        }

        return $activeVehicle;
    }

    private function getVehicleStats($vehicle)
    {
        if (!$vehicle) {
            return null;
        }

        $entries = FuelEntry::where('vehicle_id', $vehicle->id)
            ->where('user_id', Auth::id())
            ->orderBy('date', 'asc')
            ->get();

        if ($entries->isEmpty()) {
            return null;
        }

        $firstEntry = $entries->first();
        $lastEntry = $entries->last();
        $totalKm = $lastEntry->odometer - ($firstEntry->odometer ?? 0);
        $totalLiter = $entries->sum('liters');
        $totalCost = $entries->sum('total_price');

        // Hitung rata-rata efisiensi
        $efficiencies = [];
        $prevOdometer = null;
        $prevLiters = null;

        foreach ($entries as $entry) {
            if ($prevOdometer !== null && $prevLiters !== null) {
                $distance = $entry->odometer - $prevOdometer;
                if ($distance > 0 && $prevLiters > 0) {
                    $efficiencies[] = $distance / $prevLiters;
                }
            }
            $prevOdometer = $entry->odometer;
            $prevLiters = $entry->liters;
        }

        $avgEfficiency = !empty($efficiencies) ? array_sum($efficiencies) / count($efficiencies) : 0;

        return [
            'total_km' => $totalKm,
            'total_liter' => $totalLiter,
            'total_cost' => $totalCost,
            'avg_km_per_liter' => $totalLiter > 0 ? $totalKm / $totalLiter : 0,
            'avg_efficiency' => $avgEfficiency,
            'entries_count' => $entries->count(),
        ];
    }

    private function getRecentEntries($vehicle, $limit = 5)
    {
        if (!$vehicle) {
            return collect();
        }

        return FuelEntry::where('vehicle_id', $vehicle->id)
            ->where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function index()
    {
        $vehicles = Auth::user()->vehicles()->latest()->get();
        $vehicle = $this->getActiveVehicle();

        // Get statistics for active vehicle
        $stats = $this->getVehicleStats($vehicle);

        // Get recent entries for active vehicle
        $recentEntries = $this->getRecentEntries($vehicle);

        return view('dashboard.home', compact('vehicle', 'vehicles', 'stats', 'recentEntries'));
    }


//   private function getActiveVehicle()
//     {
//         $user = Auth::user();

//         // Cari kendaraan yang is_active-nya true
//         $activeVehicle = $user->vehicles()->where('is_active', true)->first();

//         // Kalau tidak ada yang aktif (misal baru pertama kali tambah), ambil yang terbaru saja
//         if (!$activeVehicle) {
//             $activeVehicle = $user->vehicles()->latest()->first();
//         }

//         return $activeVehicle;
//     }

//     public function index()
//     {
//          $vehicles = Auth::user()->vehicles()->latest()->get();
//         $vehicle = $this->getActiveVehicle();
//         return view('dashboard.home', compact('vehicle','vehicles'));
//     }

    public function history()
    {
       $vehicle = $this->getActiveVehicle();

        // Ambil riwayat bensin dari kendaraan yang aktif.
        // (Jika $vehicle kosong, kembalikan array kosong)
        $entries = $vehicle ? $vehicle->fuelEntries : [];

        return view('dashboard.history', compact('vehicle', 'entries'));
    }

   public function stats()
    {
        $vehicle = $this->getActiveVehicle();

        $chartData = [];
        $avgKml = 0;
        $costPerKm = 0;

        if ($vehicle) {
            // --- 1. PERBAIKAN KALKULASI EFISIENSI ---
            // Ambil datanya dulu, baru diurutkan menggunakan Collection (sortBy)
            // Ini mencegah bentrok dengan default orderBy di Model
            $entries = $vehicle->fuelEntries->sortBy('odometer')->values();

            if ($entries->count() > 1) {
                $firstOdo = $entries->first()->odometer;
                $lastOdo = $entries->last()->odometer;
                $totalDistance = $lastOdo - $firstOdo;

                // Hitung total liter & total biaya (kecuali pengisian pertama)
                $entriesForCalc = $entries->slice(1);
                $totalLiters = $entriesForCalc->sum('liters');
                $totalSpend = $entriesForCalc->sum('total_price');

                // Pastikan jaraknya positif (tidak minus)
                if ($totalDistance > 0 && $totalLiters > 0) {
                    $avgKml = $totalDistance / $totalLiters;
                    $costPerKm = $totalSpend / $totalDistance;
                }
            }

            // --- 2. PERBAIKAN GRAFIK 7 HARI ---
            $last7Days = collect();
            $today = \Carbon\Carbon::today(); // Pakai today() agar jamnya di-reset ke 00:00:00

            // Looping dari 6 hari lalu sampai hari ini
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $dateString = $date->format('Y-m-d');

                // Cari total pengeluaran di tanggal tersebut menggunakan Collection
                $dailySpend = $vehicle->fuelEntries->where('date', $dateString)->sum('total_price');

                // Label Hari Bahasa Indonesia
                $dayNames = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
                $dayLabel = $dayNames[$date->format('D')];

                $last7Days->push([
                    'label' => $dayLabel,
                    'total' => $dailySpend
                ]);
            }

            // Hitung persentase tinggi batang grafik
            $maxSpend = $last7Days->max('total');

            $chartData = $last7Days->map(function($day) use ($maxSpend) {
                $percentage = $maxSpend > 0 ? ($day['total'] / $maxSpend) * 100 : 0;
                return [
                    'label' => $day['label'],
                    'percentage' => max($percentage, 5), // Minimal tinggi 5% agar batangnya tetap terlihat
                    'total' => $day['total']
                ];
            });
        }

        return view('dashboard.stats', compact('vehicle', 'chartData', 'avgKml', 'costPerKm'));
    }

   public function account()
    {
        return view('dashboard.profile');
    }

   /**
     * Logika untuk merubah kolom is_active di database
     */
    public function switchVehicle(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id'
        ]);

        $user = Auth::user();

        // 1. Matikan (is_active = false) SEMUA kendaraan milik user ini
        $user->vehicles()->update(['is_active' => false]);

        // 2. Aktifkan HANYA kendaraan yang dipilih
        $vehicle = $user->vehicles()->find($request->vehicle_id);
        if ($vehicle) {
            $vehicle->update(['is_active' => true]);
            return back()->with('success', 'Kendaraan aktif berhasil diubah menjadi ' . $vehicle->name);
        }

        return back()->with('error', 'Gagal mengganti kendaraan.');
    }
}
