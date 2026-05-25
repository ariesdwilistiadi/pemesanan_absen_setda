<?php
$user = App\Models\User::find(2);
$user->email_verified_at = now();
$user->save();

if (class_exists('Spatie\Permission\Models\Permission')) {
    $permissions = Spatie\Permission\Models\Permission::all();
    $user->syncPermissions($permissions);
    echo "Permissions assigned.\n";
} else {
    echo "Spatie Permission not found.\n";
}
