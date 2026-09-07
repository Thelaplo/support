<?php

namespace Tickets\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;
use function faker;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'author_id' => User::factory(),
            'body' => faker()->paragraphs(1),
        ];
    }
}