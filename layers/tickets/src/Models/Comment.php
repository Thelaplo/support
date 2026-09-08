<?php

namespace Tickets\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tickets\Database\Factories\CommentFactory;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int $author_id
 * @property string $body
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Ticket $ticket
 * @property User $author
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'author_id',
        'body',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }
}
