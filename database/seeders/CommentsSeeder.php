<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comment;

class CommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 30; $i++) {
            Comment::factory()->state([
                'comment_reply_id' => null,
            ])->create();
        }
        for ($i = 0; $i < 20; $i++) {
            Comment::factory()->state([
                'comment_reply_id' => rand(1, 30),
            ])->create();
        }
    }
}
