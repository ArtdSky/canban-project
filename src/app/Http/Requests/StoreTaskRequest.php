<?php

namespace App\Http\Requests;

use App\StateMachines\TaskStatusStateMachine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['sometimes', 'string', Rule::in([
                TaskStatusStateMachine::STATUS_TODO,
                TaskStatusStateMachine::STATUS_IN_PROGRESS,
                TaskStatusStateMachine::STATUS_DONE,
            ])],
            'due_date' => ['nullable', 'date'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
            'observer_ids' => ['nullable', 'array'],
            'observer_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Наименование задачи обязательно.',
            'description.required' => 'Содержание задачи обязательно.',
            'status.in' => 'Недопустимый статус задачи.',
            'due_date.date' => 'Срок исполнения должен быть валидной датой.',
            'assignee_ids.array' => 'Исполнители должны быть массивом.',
            'assignee_ids.*.integer' => 'ID исполнителя должен быть числом.',
            'assignee_ids.*.exists' => 'Один из исполнителей не найден.',
            'observer_ids.array' => 'Наблюдатели должны быть массивом.',
            'observer_ids.*.integer' => 'ID наблюдателя должен быть числом.',
            'observer_ids.*.exists' => 'Один из наблюдателей не найден.',
        ];
    }
}
