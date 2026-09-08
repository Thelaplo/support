<?php

namespace Tickets\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tickets\Database\Factories\TicketFactory;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;

/**
 * @property int $id
 * @property int $requester_id
 * @property int|null $technician_id
 * @property string $title
 * @property string|null $description
 * @property TicketStatus|string $status
 * @property TicketPriority|string $priority
 * @property Carbon|null $resolved_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $comments_count
 * @property User $requester
 * @property User|null $technician
 */
class Ticket extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    protected $fillable = [
        'requester_id',
        'technician_id',
        'title',
        'description',
        'status',
        'priority',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function prunable()
    {
        return static::where('deleted_at', '<=', now()->subDays(30));
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function getResolutionTimeInHours(): ?float
    {
        if (!$this->resolved_at) {
            return null;
        }

        return (float) $this->created_at->diffInHours($this->resolved_at);
    }

    public function resolutionDurationInMinutes(): ?int
    {
        if (!$this->resolved_at) {
            return null;
        }

        return (int) $this->created_at->diffInMinutes($this->resolved_at);
    }

    public function getTargetSlaHours(): int
    {
        $priorityValue = is_object($this->priority) ? $this->priority->value : $this->priority;

        return match ($priorityValue) {
            'critical' => 4,
            'high' => 8,
            'normal' => 24,
            'low' => 72,
            default => 24,
        };
    }

    public function isSlaBreached(): bool
    {
        $limit = $this->created_at->copy()->addHours($this->getTargetSlaHours());

        if ($this->resolved_at) {
            return $this->resolved_at->greaterThan($limit);
        }

        return now()->greaterThan($limit);
    }

    protected static function newFactory(): TicketFactory
    {
        return TicketFactory::new();
    }
}
