<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\Destination;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use App\Models\User;

trait CreatesCatalog
{
    protected function admin(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => User::ROLE_ADMIN]);
    }

    protected function customer(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => User::ROLE_CUSTOMER]);
    }

    /**
     * @param  array{quota?:int, price?:float}  $options
     * @return array{0: Destination, 1: TicketType}
     */
    protected function destinationWithTicket(string $date, int $quota = 10, float $price = 50000): array
    {
        $category = Category::create([
            'name' => 'Pantai',
            'slug' => 'pantai-'.uniqid(),
        ]);

        $destination = Destination::create([
            'category_id' => $category->id,
            'name' => 'Pantai Uji',
            'slug' => 'pantai-uji-'.uniqid(),
            'description' => 'Destinasi untuk pengujian.',
            'location' => 'Bali',
            'status' => Destination::STATUS_ACTIVE,
        ]);

        $ticketType = TicketType::create([
            'destination_id' => $destination->id,
            'name' => 'Tiket Dewasa',
            'price' => $price,
            'default_quota' => $quota,
            'status' => TicketType::STATUS_ACTIVE,
        ]);

        TicketAvailability::create([
            'ticket_type_id' => $ticketType->id,
            'visit_date' => $date,
            'quota' => $quota,
            'sold' => 0,
        ]);

        return [$destination, $ticketType];
    }
}
