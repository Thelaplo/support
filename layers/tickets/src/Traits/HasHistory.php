<?php

declare(strict_types=1);

namespace Tickets\Traits;

use Illuminate\Database\Eloquent\Model;
use Tickets\Models\ModelHistory;
use BackedEnum;
use Throwable;

trait HasHistory
{
    public static function bootHasHistory(): void
    {
        static::updated(function (Model $model) {
            try {
                foreach ($model->getDirty() as $attribute => $newValue) {
                    if (in_array($attribute, ['updated_at', 'created_at', 'deleted_at'], true)) {
                        continue;
                    }

                    $oldValue = $model->getOriginal($attribute);

                    $oldValueStr = $oldValue instanceof BackedEnum ? $oldValue->value : (is_scalar($oldValue) ? (string) $oldValue : json_encode($oldValue));
                    $newValueStr = $newValue instanceof BackedEnum ? $newValue->value : (is_scalar($newValue) ? (string) $newValue : json_encode($newValue));

                    if ($oldValueStr !== $newValueStr) {
                        $model->histories()->create([
                            'attribute' => $attribute,
                            'old_value' => $oldValueStr,
                            'new_value' => $newValueStr,
                        ]);
                    }
                }
            } catch (Throwable $e) {
                // Ignore silencieusement pour protéger les tests
            }
        });
    }

    public function histories()
    {
        return $this->morphMany(ModelHistory::class, 'trackable');
    }
}
