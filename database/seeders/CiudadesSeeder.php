<?php

namespace Database\Seeders;

use Database\Seeders\ciudades\AmazonasSeeder;
use Database\Seeders\ciudades\AntioquiaSeeder;
use Database\Seeders\ciudades\AraucaSeeder;
use Database\Seeders\ciudades\AtlanticoSeeder;
use Database\Seeders\ciudades\BogotaSeeder;
use Database\Seeders\ciudades\BolivarSeeder;
use Database\Seeders\ciudades\BoyacaSeeder;
use Database\Seeders\ciudades\CaldasSeeder;
use Database\Seeders\ciudades\CaquetaSeeder;
use Database\Seeders\ciudades\CasanareSeeder;
use Database\Seeders\ciudades\CaucaSeeder;
use Database\Seeders\ciudades\CesarSeeder;
use Database\Seeders\ciudades\ChocoSeeder;
use Database\Seeders\ciudades\CordobaSeeder;
use Database\Seeders\ciudades\CundinamarcaSeeder;
use Database\Seeders\ciudades\GuainiaSeeder;
use Database\Seeders\ciudades\GuaviareSeeder;
use Database\Seeders\ciudades\HuilaSeeder;
use Database\Seeders\ciudades\LaGuajiraSeeder;
use Database\Seeders\ciudades\MagdalenaSeeder;
use Database\Seeders\ciudades\MetaSeeder;
use Database\Seeders\ciudades\NarinoSeeder;
use Database\Seeders\ciudades\NorteDeSantanderSeeder;
use Database\Seeders\ciudades\PutumayoSeeder;
use Database\Seeders\ciudades\QuindioSeeder;
use Database\Seeders\ciudades\RisaraldaSeeder;
use Database\Seeders\ciudades\SanAndresSeeder;
use Database\Seeders\ciudades\SantanderSeeder;
use Database\Seeders\ciudades\SucreSeeder;
use Database\Seeders\ciudades\TolimaSeeder;
use Database\Seeders\ciudades\ValleDelCaucaSeeder;
use Database\Seeders\ciudades\VaupesSeeder;
use Database\Seeders\ciudades\VichadaSeeder;
use Illuminate\Database\Seeder;

class CiudadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $this->call([
            AmazonasSeeder::class,
            AntioquiaSeeder::class,
            AraucaSeeder::class,
            AtlanticoSeeder::class,
            BogotaSeeder::class,
            BolivarSeeder::class,
            BoyacaSeeder::class,
            CaldasSeeder::class,
            CaquetaSeeder::class,
            CasanareSeeder::class,
            CaucaSeeder::class,
            CesarSeeder::class,
            ChocoSeeder::class,
            CordobaSeeder::class,
            CundinamarcaSeeder::class,
            GuainiaSeeder::class,
            GuaviareSeeder::class,
            HuilaSeeder::class,
            LaGuajiraSeeder::class,
            MagdalenaSeeder::class,
            MetaSeeder::class,
            NarinoSeeder::class,
            NorteDeSantanderSeeder::class,
            PutumayoSeeder::class,
            QuindioSeeder::class,
            RisaraldaSeeder::class,
            SanAndresSeeder::class,
            SantanderSeeder::class,
            SucreSeeder::class,
            TolimaSeeder::class,
            ValleDelCaucaSeeder::class,
            VaupesSeeder::class,
            VichadaSeeder::class,
        ]);

    }
}
