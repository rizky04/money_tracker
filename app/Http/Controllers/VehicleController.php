<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    /**
     * Menampilkan daftar kendaraan di halaman Garasi.
     */
    public function index()
    {
        // Ambil semua kendaraan milik user yang sedang login, urutkan dari yang terbaru
        // $vehicles = Auth::user()->vehicles()->latest()->get();

        // return view('vehicles.index', compact('vehicles'));
        // Memberi tahu editor bahwa $user adalah model App\Models\User
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Sekarang Intelephense akan mengenali metode vehicles()
        $vehicles = $user->vehicles()->latest()->get();

        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Menyimpan data kendaraan baru ke database.
     */
    // public function store(Request $request)
    // {
    //     // 1. Validasi Input
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'license_plate' => 'required|string|max:50',
    //         'fuel_type_default' => 'nullable|string|max:50',
    //         'odometer_initial' => 'required|integer|min:0',
    //     ], [
    //         // Pesan error kustom agar lebih ramah dibaca
    //         'name.required' => 'Nama kendaraan wajib diisi.',
    //         'license_plate.required' => 'Plat nomor wajib diisi.',
    //         'odometer_initial.required' => 'KM Awal wajib diisi dengan angka.',
    //     ]);

    //     // 2. Tambahkan ID user yang sedang login secara otomatis
    //     $validated['user_id'] = Auth::id();

    //     // 3. Simpan ke database
    //     Vehicle::create($validated);

    //     // 4. Kembali ke halaman garasi dengan pesan sukses
    //     return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil ditambahkan ke garasi!');
    // }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:50',
            'fuel_type_default' => 'nullable|string|max:50',
            'odometer_initial' => 'required|integer|min:0',
        ]);

        $validated['user_id'] = Auth::id();

        // Cek jika ini adalah kendaraan pertama yang ditambahkan
        if (Auth::user()->vehicles()->count() === 0) {
            $validated['is_active'] = true; // Otomatis aktif!
        }

        Vehicle::create($validated);

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil ditambahkan ke garasi!');
    }

    /**
     * Mengupdate data kendaraan.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        // Keamanan: Pastikan kendaraan ini milik user yang sedang login
        if ($vehicle->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:50',
            'fuel_type_default' => 'nullable|string|max:50',
            'odometer_initial' => 'required|integer|min:0',
        ]);

        $vehicle->update($validated);

        return redirect()->route('vehicles.index')->with('success', 'Data kendaraan berhasil diperbarui!');
    }

    /**
     * Menghapus kendaraan dari database.
     */
    public function destroy(Vehicle $vehicle)
    {
        // Keamanan: Pastikan kendaraan ini milik user yang sedang login
        if ($vehicle->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil dihapus!');
    }
}
