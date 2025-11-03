<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskParticipant extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'role', // creator, assignee, observer
    ];

    /**
     * Задача
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Пользователь
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
