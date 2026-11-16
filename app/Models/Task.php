<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $due_date
 * @property bool $is_completed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'title', 'description', 'due_date', 'is_completed'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_completed' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the task.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Limit the query to tasks owned by one user.
     *
     * @param  Builder<Task>  $query
     */
    public function scopeOwnedBy(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Resolve a route binding only when the task belongs to the authenticated user.
     *
     * Another user's task is treated as missing, so its details are not revealed.
     *
     * @param  Model|Builder<static>|\Illuminate\Contracts\Database\Eloquent\Builder|Relation<Model, Model, mixed>  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Builder<static>
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        /** @var Builder<static> $builder */
        $builder = parent::resolveRouteBindingQuery($query, $value, $field);

        return $builder->ownedBy((int) (auth()->id() ?? 0));
    }
}
