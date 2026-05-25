<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('manage_user_permissions');
    }

    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'string', 'distinct', Rule::exists('permissions', 'name')],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('permissions') && is_array($this->permissions)) {
            $this->merge([
                'permissions' => array_values(array_unique($this->permissions)),
            ]);
        }
    }
}
