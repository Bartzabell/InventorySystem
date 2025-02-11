<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Inventory;
use App\Models\User;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'item_code' => strtoupper($this->faker->bothify('ITEM-####')),
            'item_description' => $this->faker->sentence(3),
            'item_qty' => $this->faker->numberBetween(1, 100),
            'item_price' => $this->faker->randomFloat(2, 10, 500),
            'total_amount' => function (array $attributes) {
                return $attributes['item_qty'] * $attributes['item_price'];
            },
            'created_by' => User::inRandomOrder()->value('id'),
            'updated_by' => User::inRandomOrder()->value('id'),
            'deleted_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
