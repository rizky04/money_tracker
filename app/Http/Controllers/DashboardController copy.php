<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // public function index()
    // {
    //     // Ambil kendaraan terakhir yang ditambahkan oleh user
    //     $vehicle = Auth::user()->vehicles()->latest()->first();

    //     // Nanti kalau tabel fuel_entries sudah ada, kita panggil riwayat di sini
    //     // $recentEntries = $vehicle ? $vehicle->fuelEntries()->latest()->take(5)->get() : [];

    //     // Lempar data $vehicle ke halaman dashboard
    //     return view('dashboard', compact('vehicle'));
    // }
    // public function index()
    // {
    //     $vehicle = Auth::user()->vehicles()->latest()->first();
    //     return view('dashboard.home', compact('vehicle'));
    // }

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

    public function index()
    {
         $vehicles = Auth::user()->vehicles()->latest()->get();
        $vehicle = $this->getActiveVehicle();
        return view('dashboard.home', compact('vehicle','vehicles'));
    }

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
        $vehicles = Auth::user()->vehicles()->latest()->get();
        $activeVehicle = $this->getActiveVehicle();

        return view('dashboard.profile', compact('vehicles', 'activeVehicle'));
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
