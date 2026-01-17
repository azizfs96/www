<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tenant;

class TenantPolicy
{
    /**
     * Determine if user can manage tenants (super admin only)
     */
    public function manageTenants(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if user can view tenant
     */
    public function viewTenant(User $user, Tenant $tenant): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->tenants()->where('tenants.id', $tenant->id)->exists();
    }

    /**
     * Determine if user can manage tenant users
     */
    public function manageTenantUsers(User $user, Tenant $tenant): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Check if user is admin of this tenant
        $pivot = $user->tenants()->where('tenants.id', $tenant->id)->first()?->pivot;
        return $pivot && $pivot->role === 'admin';
    }
}
