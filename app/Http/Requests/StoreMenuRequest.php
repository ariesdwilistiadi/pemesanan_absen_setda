<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Support\RoutePermissionCatalog;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create_menus');
    }

    public function rules(): array
    {
        $catalog = app(RoutePermissionCatalog::class);

        return [
            'name' => ['required', 'string', 'max:255'],
            'route' => ['nullable', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail) use ($catalog) {
                if ($value && !$catalog->routeExists($value)) {
                    $fail('Route menu harus memakai route aktif yang terdaftar.');
                }
            }],
            'icon' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'permission_name' => ['nullable', 'string', 'exists:permissions,name'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $catalog = app(RoutePermissionCatalog::class);
            $routeName = $this->string('route')->toString();
            $permissionName = $this->filled('permission_name') ? $this->string('permission_name')->toString() : null;

            if (!$routeName) {
                return;
            }

            $routePermissions = $catalog->routePermissionsByName($routeName);

            if (count($routePermissions) === 1 && $permissionName !== $routePermissions[0]) {
                $validator->errors()->add(
                    'permission_name',
                    'Permission menu harus sama dengan permission route agar menu dan akses user tetap sinkron.'
                );
            }
        });
    }
}
