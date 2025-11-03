<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
        // Все участники (создатель, исполнители, наблюдатели) хранятся в task_participants
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
        ];
    }

    /**
     * Получить создателя задачи (через participants с ролью creator)
     */
    public function getCreatorAttribute(): ?User
    {
        $participant = $this->participants()
            ->where('role', 'creator')
            ->with('user')
            ->first();

        return $participant ? $participant->user : null;
    }

    /**
     * Получить ID создателя задачи
     */
    public function getCreatorIdAttribute(): ?int
    {
        $participant = $this->participants()
            ->where('role', 'creator')
            ->first();

        return $participant ? $participant->user_id : null;
    }

    /**
     * Получить всех исполнителей задачи (через participants с ролью assignee)
     */
    public function getAssigneesAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->participants()
            ->where('role', 'assignee')
            ->get()
            ->map(function ($participant) {
                return $participant->user;
            })
            ->filter();
    }

    /**
     * Получить всех наблюдателей задачи (через participants с ролью observer)
     */
    public function getObserversAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->participants()
            ->where('role', 'observer')
            ->get()
            ->map(function ($participant) {
                return $participant->user;
            })
            ->filter();
    }

    /**
     * Участники задачи (many-to-many через TaskParticipant)
     */
    public function participants(): HasMany
    {
        return $this->hasMany(TaskParticipant::class);
    }

    /**
     * Пользователи-участники через pivot таблицу
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_participants')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Комментарии к задаче
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Проверка, является ли пользователь участником задачи
     */
    public function hasParticipant(int $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    /**
     * Получить роль пользователя в задаче (возвращает первую найденную роль)
     * @deprecated Используйте getUserRoles() для получения всех ролей
     */
    public function getUserRole(int $userId): ?string
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        return $participant ? $participant->role : null;
    }

    /**
     * Получить все роли пользователя в задаче
     */
    public function getUserRoles(int $userId): array
    {
        return $this->participants()
            ->where('user_id', $userId)
            ->pluck('role')
            ->toArray();
    }
}
