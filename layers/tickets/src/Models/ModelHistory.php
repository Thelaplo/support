<?php

declare(strict_types=1);

namespace Tickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Prunable;
use App\Models\User;

class ModelHistory extends Model
{
    use Prunable;

    protected $table = 'model_histories';

    protected $fillable = [
        'user_id',
        'attribute',
        'old_value',
        'new_value',
    ];

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subMonths(6));
    }
}
