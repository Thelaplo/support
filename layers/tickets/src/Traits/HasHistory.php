<?php

declare(strict_types=1);

namespace Tickets\Traits;

use Illuminate\Database\Eloquent\Model;
use Tickets\Models\ModelHistory;

trait HasHistory
{
    public static function bootHasHistory(): void
    {
        static::updated(function (Model $model) {
            foreach ($model->getDirty() as $attribute => $newValue) {
                if ($attribute === 'updated_at') {
                    continue;
                }

                $oldValue = $model->getOriginal($attribute);

                if ($oldValue !== $newValue) {
                    $model->histories()->create([
                        'user_id' => auth()->id(),
                        'attribute' => $attribute,
                        'old_value' => (string) $oldValue,
                        'new_value' => (string) $newValue,
                    ]);
                }
            }
        });
    }

    public function histories()
    {
        return $this->morphMany(ModelHistory::class, 'trackable');
    }
}
