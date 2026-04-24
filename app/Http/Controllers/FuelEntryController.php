<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFuelEntryRequest;
use App\Http\Requests\UpdateFuelEntryRequest;
use App\Models\FuelEntry;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FuelEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       $user = Auth::user();
    $vehicle = Vehicle::where('user_id', $user->id)->where('is_active', true)->first();

    if (!$vehicle) {
        return redirect()->route('vehicles.index')->with('error', 'Pilih kendaraan terlebih dahulu');
    }

    return view('fuel.create', compact('vehicle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreFuelEntryRequest $request)
    // {
    //     //
    // }

    public function store(Request $request)
    {
        // 1. Validasi semua input dari form frontend
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'odometer' => 'required|integer|min:0',
            'location_name' => 'nullable|string|max:255',
            'fuel_type' => 'required|string|max:50',
            'price_per_liter' => 'required|numeric|min:0',
            'liters' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
        ]);

        // 2. Keamanan: Pastikan kendaraan ini benar-benar milik user yang sedang login
        $vehicle = Vehicle::where('id', $request->vehicle_id)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        // 3. Tambahkan data otomatis di belakang layar
        $validated['user_id'] = Auth::id();
        $validated['is_ai_generated'] = false; // Karena ini input manual

        // 4. Simpan ke tabel fuel_entries
        FuelEntry::create($validated);

        // 5. UPDATE ODOMETER KENDARAAN (Jika angka KM baru lebih besar dari sebelumnya)
        if ($validated['odometer'] > $vehicle->odometer_initial) {
            $vehicle->update(['odometer_initial' => $validated['odometer']]);
        }

        return back()->with('success', 'Data pengisian bensin berhasil disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelEntry $fuelEntry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FuelEntry $fuelEntry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, FuelEntry $fuelEntry)
    {
        // Keamanan: Pastikan data ini milik user yang sedang login
        if ($fuelEntry->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'odometer' => 'required|integer|min:0',
            'location_name' => 'nullable|string|max:255',
            'fuel_type' => 'required|string|max:50',
            'price_per_liter' => 'required|numeric|min:0',
            'liters' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
        ]);

        $fuelEntry->update($validated);

        return back()->with('success', 'Riwayat pengisian berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FuelEntry $fuelEntry)
    {
        // Keamanan: Pastikan data ini milik user yang sedang login
        if ($fuelEntry->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $fuelEntry->delete();

        return back()->with('success', 'Riwayat pengisian berhasil dihapus!');
    }
}
