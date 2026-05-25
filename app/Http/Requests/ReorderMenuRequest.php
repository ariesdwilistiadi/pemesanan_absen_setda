<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('reorder_menus');
    }

    public function rules(): array
    {
        return [
            'menus' => ['required', 'array'],
            'menus.*.id' => ['required', 'integer', 'exists:menus,id'],
            'menus.*.order' => ['required', 'integer'],
        ];
    }
}
