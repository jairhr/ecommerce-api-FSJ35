<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Laptop Lenovo IdeaPad 3',
            'description' => 'Laptop Lenovo con procesador Intel Core i5, 8GB RAM y 512GB SSD.',
            'price' => 699.99,
            'stock' => 15,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Monitor LG 24 pulgadas',
            'description' => 'Monitor Full HD de 24 pulgadas para oficina y entretenimiento.',
            'price' => 179.99,
            'stock' => 20,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Teclado Mecánico RGB',
            'description' => 'Teclado mecánico con iluminación RGB y switches mecánicos.',
            'price' => 59.99,
            'stock' => 30,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Mouse Logitech',
            'description' => 'Mouse inalámbrico ergonómico para uso diario.',
            'price' => 29.99,
            'stock' => 40,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Audífonos Sony WH-1000XM5',
            'description' => 'Audífonos inalámbricos con cancelación activa de ruido.',
            'price' => 349.99,
            'stock' => 10,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Webcam Logitech C920',
            'description' => 'Webcam Full HD 1080p para videollamadas y streaming.',
            'price' => 89.99,
            'stock' => 25,
            'is_active' => true,
        ]);
    }
}