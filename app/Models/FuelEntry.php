<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FuelEntry extends Model
{
    protected $fillable = [
        'vehicle_id', 'user_id', 'date', 'fuel_type', 'liters',
        'price_per_liter', 'total_price', 'odometer', 'location_name',
        'receipt_image', 'is_ai_generated'
    ];

    protected $casts = [
        'date' => 'date',
        'liters' => 'decimal:2',
        'price_per_liter' => 'decimal:2',
        'total_price' => 'decimal:2',
        'fuel_efficiency' => 'decimal:2',
        'cost_per_km' => 'decimal:2',
        'search_metadata' => 'array',
    ];

    // Auto-generate search text saat menyimpan
    protected static function booted()
    {
        static::saving(function ($fuelEntry) {
            // Generate searchable text
            $fuelEntry->search_text = implode(' ', [
                $fuelEntry->fuel_type,
                $fuelEntry->liters . ' liter',
                'Rp ' . number_format($fuelEntry->price_per_liter, 0, ',', '.'),
                $fuelEntry->location_name ?? '',
                'Odometer ' . $fuelEntry->odometer . ' km',
                $fuelEntry->vehicle?->name ?? '',
                $fuelEntry->vehicle?->license_plate ?? '',
            ]);

            // Hitung efisiensi jika ada data sebelumnya
            $fuelEntry->calculateEfficiency();
        });

        static::saved(function ($fuelEntry) {
            // Update semua entries yang terpengaruh
            $fuelEntry->updateAffectedEntries();
        });
    }

    public function calculateEfficiency()
    {
        $previousEntry = FuelEntry::where('vehicle_id', $this->vehicle_id)
            ->where('odometer', '<', $this->odometer)
            ->where('id', '!=', $this->id)
            ->orderBy('odometer', 'desc')
            ->first();

        if ($previousEntry) {
            $distance = $this->odometer - $previousEntry->odometer;
            if ($distance > 0 && $this->liters > 0) {
                $this->fuel_efficiency = $distance / $this->liters;
                $this->cost_per_km = $this->total_price / $distance;
            }
        }

        return $this;
    }

    public function updateAffectedEntries()
    {
        // Update entries setelah ini
        $nextEntries = FuelEntry::where('vehicle_id', $this->vehicle_id)
            ->where('odometer', '>', $this->odometer)
            ->orderBy('odometer', 'asc')
            ->get();

        foreach ($nextEntries as $entry) {
            $entry->calculateEfficiency();
            $entry->saveQuietly();
        }
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope untuk pencarian
    public function scopeSearchByKeyword(Builder $query, string $keyword)
    {
        return $query->whereFullText('search_text', $keyword);
    }

    // Scope untuk filter berdasarkan periode
    public function scopePeriod(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
