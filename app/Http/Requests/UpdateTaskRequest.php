<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Prepare the data for validation.
     *
     * A missing completed checkbox means the task is not completed.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'is_completed' => false,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The owner is never taken from the form.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'is_completed' => ['boolean'],
        ];
    }
}
