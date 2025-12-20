<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\ApartmentImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApartmentSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();

        $totalApartments = 6;

        for ($i = 1; $i <= $totalApartments; $i++) {

            $areaId = $i <= 3 ? 1 : 4;

            $apartment = Apartment::create([
                'area_id'     => $areaId,
                'owner_id'    => $owner->id,
                'price'       => rand(300, 800),
                'space'       => rand(80, 200),
                'direction'   => 'north',
                'rooms_count' => rand(2, 5),
                'description' => 'شقة جميلة ومناسبة للسكن',
            ]);

            $sourcePath = database_path("seeders/assets/apartments/apartment{$i}");

            $images = glob($sourcePath . '/*.{jpg,jpeg,png}', GLOB_BRACE);

            foreach ($images as $index => $image) {

                $fileName = Str::random(20) . '.' . pathinfo($image, PATHINFO_EXTENSION);

                $storagePath = "{$owner->id}/{$apartment->id}/{$fileName}";

                Storage::disk('apartment')->put(
                    $storagePath,
                    file_get_contents($image)
                );

                ApartmentImage::create([
                    'apartment_id' => $apartment->id,
                    'path'         => $storagePath,
                    'is_main'      => $index === 0,
                ]);
            }
        }

        
    }
}
