<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(2, true), // 商品名
            'brand' => $this->faker->optional()->company(), // ブランド名（null も可）
            'description' => $this->faker->paragraph(), // 商品説明
            'condition_id' => $this->faker->numberBetween(1, 5), // 1〜5程度のランダム値
            'price' => $this->faker->numberBetween(1000, 10000), // 価格
            'image_path' => 'images/sample.jpg', // ダミー画像パス
        ];
    }
}
