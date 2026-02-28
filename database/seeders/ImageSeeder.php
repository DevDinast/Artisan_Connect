<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Image;
use App\Models\Oeuvre;

class ImageSeeder extends Seeder
{
    public function run(): void
    {
        $oeuvres = Oeuvre::all();

        foreach ($oeuvres as $oeuvre) {
            for ($i = 1; $i <= 2; $i++) {
                Image::create([
                    'oeuvre_id' => $oeuvre->id,
                    'chemin' => 'oeuvres/test_image_' . $i . '.jpg',
                    'is_principale' => $i === 1,
                ]);
            }
        }
    }
}
