<?php

namespace Tickets\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tickets\Database\Factories\TicketFactory;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;

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

    public function prunable(): Builder
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

    protected static function newFactory(): TicketFactory
    {
        return TicketFactory::new();
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Tickets\Models\TicketAttachment::class);
    }

    public function resolutionDurationInMinutes(): ?int
    {
        if (!$this->resolved_at || !$this->created_at) {
            return null;
        }
        return (int) abs($this->created_at->diffInMinutes($this->resolved_at));
    }

    public function isSlaBreached(): bool
    {
        $slaLimitsInHours = [
            'critical' => 4,
            'high' => 24,
            'normal' => 48,
            'low' => 72,
        ];
        $priority = is_object($this->priority) ? $this->priority->value : (string) $this->priority;
        $limitHours = $slaLimitsInHours[$priority] ?? 48;
        $breachDeadline = $this->created_at->copy()->addHours($limitHours);
        $referenceTime = $this->resolved_at ?? now();
        return $referenceTime->isAfter($breachDeadline);
    }
}

