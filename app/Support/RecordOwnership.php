<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class RecordOwnership
{
    public static function scopeOwned(Builder $query, User $user, ?string $legacyUsernameColumn = null): Builder
    {
        if (self::canAccessAllRecords($user)) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($user, $legacyUsernameColumn) {
            $builder->where('owner_user_id', $user->id);

            if ($legacyUsernameColumn) {
                $legacyNames = array_values(array_filter([
                    $user->username ?? null,
                    $user->name,
                    $user->email,
                ]));

                $builder->orWhere(function (Builder $legacyQuery) use ($legacyUsernameColumn, $legacyNames) {
                    $legacyQuery->whereNull('owner_user_id')
                        ->whereIn($legacyUsernameColumn, $legacyNames);
                });
            }
        });
    }

    public static function abortUnlessOwned(object $record, User $user, ?string $legacyUsernameField = null): void
    {
        if (self::canAccessAllRecords($user)) {
            return;
        }

        $ownerUserId = data_get($record, 'owner_user_id');

        if ($ownerUserId && (int) $ownerUserId === (int) $user->id) {
            return;
        }

        if ($legacyUsernameField) {
            $legacyValue = data_get($record, $legacyUsernameField);
            $legacyNames = array_filter([
                $user->username ?? null,
                $user->name,
                $user->email,
            ]);

            if ($ownerUserId === null && in_array($legacyValue, $legacyNames, true)) {
                return;
            }
        }

        abort(403, 'Anda tidak memiliki akses ke data ini.');
    }

    public static function canAccessAllRecords(User $user): bool
    {
        return Gate::forUser($user)->allows('access-all-records');
    }
}
