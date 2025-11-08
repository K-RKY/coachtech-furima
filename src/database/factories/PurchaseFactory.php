<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition()
    {
        return [
            'item_id' => Item::factory(),
            'user_id' => User::factory(),
            'payment_method' => 'credit_card',
            'shipping_address' => $this->faker->address(),
            'status' => 'paid',
            'amount' => $this->faker->numberBetween(1000, 10000),
            'stripe_session_id' => $this->faker->uuid(),
        ];
    }
}
