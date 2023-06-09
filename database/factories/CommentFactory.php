<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'text' => fake()->text(),
            'user_id' => random_int(1, 10),
            'comment_reply_id' => random_int(1, 20),
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ];
    }
}
