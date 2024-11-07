<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrackingSeeder extends Seeder
{
    public function run()
    {
        DB::table('trackings')->insert([
            [
                'device_id' => 1,
                'latitude' => -25.2844707,
                'longitude' => -57.5631504,
                'last_tracked_at' => Carbon::now()->subMinutes(5),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'device_id' => 2,
                'latitude' => -25.2900000,
                'longitude' => -57.5700000,
                'last_tracked_at' => Carbon::now()->subMinutes(3),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'device_id' => 3,
                'latitude' => -25.2950000,
                'longitude' => -57.5800000,
                'last_tracked_at' => Carbon::now()->subMinutes(1),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Agrega más registros si es necesario
        ]);
    }
}
