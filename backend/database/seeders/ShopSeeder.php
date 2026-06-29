<?php

namespace Database\Seeders;

use App\Models\Shop;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $headquarter = Shop::updateOrCreate(
            [
                'code' => 'matriz',
            ],
            [
                'name' => 'Metalar Matriz',
                'cnpj' => '11222333000181',
                'phone' => '71999999999',
                'zip_code' => '40000000',
                'street' => 'Rua da Matriz',
                'number' => '100',
                'complement' => null,
                'neighborhood' => 'Centro',
                'city' => 'Guanambi',
                'state' => 'BA',
            ]
        );

        $branch = Shop::updateOrCreate(
            [
                'code' => 'filial01',
            ],
            [
                'name' => 'Metalar Filial01',
                'cnpj' => '11222333000182',
                'phone' => '71999999999',
                'zip_code' => '40000000',
                'street' => 'Rua da Filial01',
                'number' => '200',
                'complement' => null,
                'neighborhood' => 'Centro',
                'city' => 'Guanambi',
                'state' => 'BA',
            ]
        );
        $headquarter->warehouse()->updateOrCreate(
            [],
            [
                'name' => 'Metalar Matriz',
            ]
        );

        $branch->warehouse()->updateOrCreate(
            [],
            [
                'name' => 'Metalar Filial01',
            ]
        );
    }
}
