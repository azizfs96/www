<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

class TenantUserController extends Controller
{
    /**
     * Display users for a tenant
     */
    public function index(Tenant $tenant)
    {
        if (!Gate::allows('manage-tenant-users', $tenant)) {
            abort(403);
        }
        
        $users = $tenant->users()->withPivot('role')->get();
        
        return view('waf.tenants.users.index', compact('tenant', 'users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create(Tenant $tenant)
    {
        if (!Gate::allows('manage-tenant-users', $tenant)) {
            abort(403);
        }
        
        return view('waf.tenants.users.create', compact('tenant'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request, Tenant $tenant)
    {
        if (!Gate::allows('manage-tenant-users', $tenant)) {
            abort(403);
        }
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
        ]);

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'tenant_id' => $tenant->id,
        ]);

        // Link user to tenant
        $tenant->users()->attach($user->id, ['role' => $data['role']]);

        return redirect()->route('tenants.users.index', $tenant)
            ->with('status', 'User created successfully!');
    }

    /**
     * Remove the specified user from tenant
     */
    public function destroy(Tenant $tenant, User $user)
    {
        if (!Gate::allows('manage-tenant-users', $tenant)) {
            abort(403);
        }
        
        // Don't allow deleting tenant admin
        if ($tenant->admin_id === $user->id) {
            return redirect()->route('tenants.users.index', $tenant)
                ->withErrors(['error' => 'Cannot delete tenant admin.']);
        }

        $tenant->users()->detach($user->id);
        
        // If user only belongs to this tenant, delete the user
        if ($user->tenants()->count() === 0) {
            $user->delete();
        }

        return redirect()->route('tenants.users.index', $tenant)
            ->with('status', 'User removed successfully!');
    }
}
