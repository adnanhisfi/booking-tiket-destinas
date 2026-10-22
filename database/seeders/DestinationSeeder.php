<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Destination;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = [
            [
                'category' => 'Pantai',
                'name' => 'Pantai Kuta Bali',
                'description' => 'Pantai ikonik di Bali dengan pasir putih, ombak untuk berselancar, dan sunset yang memukau.',
                'location' => 'Badung, Bali',
                'address' => 'Jl. Pantai Kuta, Badung, Bali',
                'latitude' => -8.7176, 'longitude' => 115.1686,
                'opening_time' => '07:00', 'closing_time' => '19:00',
                'facilities' => ['Toilet', 'Parkir', 'Mushola', 'Penyewaan Kursi Pantai', 'Lifeguard'],
                'is_featured' => true,
                'tickets' => [
                    ['name' => 'Tiket Dewasa', 'price' => 50000, 'default_quota' => 500],
                    ['name' => 'Tiket Anak', 'price' => 30000, 'default_quota' => 300],
                ],
            ],
            [
                'category' => 'Gunung',
                'name' => 'Gunung Bromo',
                'description' => 'Wisata sunrise di kawah Bromo dengan lautan pasir dan Bukit Teletubbies.',
                'location' => 'Probolinggo, Jawa Timur',
                'address' => 'Taman Nasional Bromo Tengger Semeru',
                'latitude' => -7.9425, 'longitude' => 112.9530,
                'opening_time' => '03:00', 'closing_time' => '17:00',
                'facilities' => ['Jeep 4x4', 'Toilet', 'Warung', 'Pemandu Wisata'],
                'is_featured' => true,
                'tickets' => [
                    ['name' => 'Tiket Wisatawan Domestik', 'price' => 35000, 'default_quota' => 400],
                    ['name' => 'Tiket Wisatawan Asing', 'price' => 220000, 'default_quota' => 100],
                ],
            ],
            [
                'category' => 'Taman Hiburan',
                'name' => 'Dufan Ancol',
                'description' => 'Dunia Fantasi Ancol, taman hiburan terbesar di Jakarta dengan puluhan wahana.',
                'location' => 'Jakarta Utara',
                'address' => 'Jl. Lodan Timur No.7, Ancol, Jakarta Utara',
                'latitude' => -6.1223, 'longitude' => 106.8335,
                'opening_time' => '10:00', 'closing_time' => '18:00',
                'facilities' => ['Toilet', 'Food Court', 'Loker', 'Mushola', 'Pusat Informasi'],
                'is_featured' => true,
                'tickets' => [
                    ['name' => 'Tiket Reguler Weekday', 'price' => 200000, 'default_quota' => 2000],
                    ['name' => 'Tiket Reguler Weekend', 'price' => 250000, 'default_quota' => 2500],
                ],
            ],
            [
                'category' => 'Budaya & Sejarah',
                'name' => 'Candi Borobudur',
                'description' => 'Candi Buddha terbesar di dunia, warisan budaya dunia UNESCO.',
                'location' => 'Magelang, Jawa Tengah',
                'address' => 'Jl. Badrawati, Borobudur, Magelang',
                'latitude' => -7.6079, 'longitude' => 110.2038,
                'opening_time' => '06:00', 'closing_time' => '17:00',
                'facilities' => ['Pemandu', 'Toilet', 'Museum', 'Penyewaan Payung', 'Parkir'],
                'is_featured' => true,
                'tickets' => [
                    ['name' => 'Tiket Dewasa', 'price' => 50000, 'default_quota' => 1000],
                    ['name' => 'Tiket Anak', 'price' => 25000, 'default_quota' => 500],
                ],
            ],
            [
                'category' => 'Air Terjun',
                'name' => 'Air Terjun Tegenungan',
                'description' => 'Air terjun populer dekat Ubud dengan kolam alami untuk berenang.',
                'location' => 'Gianyar, Bali',
                'address' => 'Kemenuh, Sukawati, Gianyar, Bali',
                'latitude' => -8.5752, 'longitude' => 115.2885,
                'opening_time' => '06:30', 'closing_time' => '18:30',
                'facilities' => ['Toilet', 'Loker', 'Warung', 'Spot Foto'],
                'is_featured' => false,
                'tickets' => [
                    ['name' => 'Tiket Masuk', 'price' => 25000, 'default_quota' => 300],
                ],
            ],
            [
                'category' => 'Pantai',
                'name' => 'Pantai Parangtritis',
                'description' => 'Pantai legendaris Yogyakarta dengan gumuk pasir dan sunset romantis.',
                'location' => 'Bantul, DI Yogyakarta',
                'address' => 'Jl. Parangtritis, Bantul',
                'latitude' => -8.0253, 'longitude' => 110.3290,
                'opening_time' => '06:00', 'closing_time' => '20:00',
                'facilities' => ['Toilet', 'Parkir', 'Penyewaan ATV', 'Warung'],
                'is_featured' => false,
                'tickets' => [
                    ['name' => 'Tiket Dewasa', 'price' => 15000, 'default_quota' => 600],
                    ['name' => 'Tiket Anak', 'price' => 10000, 'default_quota' => 400],
                ],
            ],
            [
                'category' => 'Gunung',
                'name' => 'Kawah Putih Ciwidey',
                'description' => 'Danau kawah vulkanik berwarna putih kehijauan di dataran tinggi Bandung.',
                'location' => 'Bandung, Jawa Barat',
                'address' => 'Ciwidey, Kabupaten Bandung',
                'latitude' => -7.1662, 'longitude' => 107.4022,
                'opening_time' => '07:00', 'closing_time' => '17:00',
                'facilities' => ['Shuttle', 'Toilet', 'Kafe', 'Spot Foto'],
                'is_featured' => false,
                'tickets' => [
                    ['name' => 'Tiket Masuk', 'price' => 30000, 'default_quota' => 800],
                    ['name' => 'Tiket + Shuttle', 'price' => 50000, 'default_quota' => 400],
                ],
            ],
            [
                'category' => 'Budaya & Sejarah',
                'name' => 'Kota Tua Jakarta',
                'description' => 'Kawasan bersejarah dengan bangunan kolonial, museum, dan wisata kuliner.',
                'location' => 'Jakarta Barat',
                'address' => 'Jl. Taman Fatahillah, Jakarta Barat',
                'latitude' => -6.1352, 'longitude' => 106.8133,
                'opening_time' => '08:00', 'closing_time' => '18:00',
                'facilities' => ['Museum', 'Sepeda Onthel', 'Kafe', 'Toilet'],
                'is_featured' => false,
                'tickets' => [
                    ['name' => 'Tiket Masuk Kawasan', 'price' => 10000, 'default_quota' => 1000],
                    ['name' => 'Paket Museum', 'price' => 40000, 'default_quota' => 200],
                ],
            ],
        ];

        foreach ($destinations as $data) {
            $category = Category::where('slug', Str::slug($data['category']))->firstOrFail();

            $destination = Destination::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'location' => $data['location'],
                    'address' => $data['address'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'opening_time' => $data['opening_time'],
                    'closing_time' => $data['closing_time'],
                    'facilities' => $data['facilities'],
                    'images' => [],
                    'thumbnail' => null,
                    'is_featured' => $data['is_featured'],
                    'status' => Destination::STATUS_ACTIVE,
                ]
            );

            foreach ($data['tickets'] as $ticket) {
                $ticketType = TicketType::updateOrCreate(
                    ['destination_id' => $destination->id, 'name' => $ticket['name']],
                    [
                        'description' => null,
                        'price' => $ticket['price'],
                        'default_quota' => $ticket['default_quota'],
                        'status' => TicketType::STATUS_ACTIVE,
                    ]
                );

                for ($day = 0; $day <= 30; $day++) {
                    $date = now()->addDays($day)->toDateString();
                    $quota = $ticket['default_quota'];

                    if ($day % 7 === 6) {
                        $quota = (int) round($quota * 1.5);
                    }

                    TicketAvailability::updateOrCreate(
                        ['ticket_type_id' => $ticketType->id, 'visit_date' => $date],
                        ['quota' => $quota]
                    );
                }
            }
        }
    }
}
