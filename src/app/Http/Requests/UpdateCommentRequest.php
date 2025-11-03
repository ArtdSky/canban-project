<?php

namespace App\Http\Requests;

use App\StateMachines\CommentStatusStateMachine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommentRequest extends FormRequest
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
            'content' => ['sometimes', 'required', 'string', 'max:5000'],
            'status' => ['sometimes', 'string', Rule::in([
                CommentStatusStateMachine::STATUS_VISIBLE,
                CommentStatusStateMachine::STATUS_HIDDEN,
            ])],
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
            'content.required' => 'Содержание комментария обязательно.',
            'content.max' => 'Комментарий не может быть длиннее 5000 символов.',
            'status.in' => 'Недопустимый статус комментария.',
        ];
    }
}
