<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['user_id', 'name', 'license_plate', 'fuel_type_default', 'odometer_initial', 'is_active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fuelEntries()
    {
        return $this->hasMany(FuelEntry::class)->orderBy('date', 'desc');
    }
}
