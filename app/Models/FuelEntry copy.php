<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelEntry extends Model
{
    protected $fillable = [
        'vehicle_id', 'user_id', 'date', 'fuel_type', 'liters',
        'price_per_liter', 'total_price', 'odometer',
        'location_name', 'receipt_image', 'is_ai_generated'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Fungsi untuk mendapatkan pengisian sebelumnya (untuk hitung KM/L)
    public function previousEntry()
    {
        return FuelEntry::where('vehicle_id', $this->vehicle_id)
            ->where('odometer', '<', $this->odometer)
            ->orderBy('odometer', 'desc')
            ->first();
    }

    // Atribut buatan: $fuelEntry->kml
    public function getKmlAttribute()
    {
        $previous = $this->previousEntry();
        if (!$previous) return 0;

        $distance = $this->odometer - $previous->odometer;
        return $distance > 0 ? round($distance / $this->liters, 2) : 0;
    }
}
