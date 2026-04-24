<?php

namespace App\Console\Commands;

use App\Models\FuelEntry;
use Illuminate\Console\Command;

class UpdateFuelEntriesCalculations extends Command
{
    protected $signature = 'fuel:update-calculations';
    protected $description = 'Update fuel efficiency and cost per km for all entries';

    public function handle()
    {
        $entries = FuelEntry::orderBy('vehicle_id')->orderBy('odometer')->get();
        $lastEntryByVehicle = [];

        $bar = $this->output->createProgressBar(count($entries));
        $bar->start();

        foreach ($entries as $entry) {
            // Update search text
            $entry->search_text = implode(' ', [
                $entry->fuel_type,
                $entry->liters . ' liter',
                'Rp ' . number_format($entry->price_per_liter, 0, ',', '.'),
                $entry->location_name ?? '',
                'Odometer ' . $entry->odometer . ' km',
            ]);

            // Calculate efficiency
            $key = $entry->vehicle_id;
            if (isset($lastEntryByVehicle[$key])) {
                $lastEntry = $lastEntryByVehicle[$key];
                $distance = $entry->odometer - $lastEntry->odometer;
                if ($distance > 0 && $entry->liters > 0) {
                    $entry->fuel_efficiency = $distance / $entry->liters;
                    $entry->cost_per_km = $entry->total_price / $distance;
                }
            }

            $entry->saveQuietly();
            $lastEntryByVehicle[$key] = $entry;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Calculations updated successfully!');
    }
}
