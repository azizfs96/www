<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants (only for super admin)
     */
    public function index()
    {
        if (!Gate::allows('manage-tenants')) {
            abort(403);
        }
        
        $tenants = Tenant::with('admin')->orderByDesc('created_at')->get();
        
        return view('waf.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create()
    {
        if (!Gate::allows('manage-tenants')) {
            abort(403);
        }
        
        return view('waf.tenants.create');
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        if (!Gate::allows('manage-tenants')) {
            abort(403);
        }
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tenants,slug',
            'description' => 'nullable|string',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Create tenant admin user
        $admin = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'role' => 'tenant_admin',
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'admin_id' => $admin->id,
            'active' => true,
        ]);

        // Link admin to tenant
        $admin->update(['tenant_id' => $tenant->id]);
        $tenant->users()->attach($admin->id, ['role' => 'admin']);

        return redirect()->route('tenants.index')
            ->with('status', 'Tenant created successfully!');
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant)
    {
        if (!Gate::allows('view-tenant', $tenant)) {
            abort(403);
        }
        
        $tenant->load(['admin', 'users']);
        
        return view('waf.tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified tenant
     */
    public function edit(Tenant $tenant)
    {
        if (!Gate::allows('manage-tenants')) {
            abort(403);
        }
        
        return view('waf.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        if (!Gate::allows('manage-tenants')) {
            abort(403);
        }
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug,' . $tenant->id,
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $tenant->update($data);

        return redirect()->route('tenants.index')
            ->with('status', 'Tenant updated successfully!');
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(Tenant $tenant)
    {
        if (!Gate::allows('manage-tenants')) {
            abort(403);
        }
        
        $tenant->delete();

        return redirect()->route('tenants.index')
            ->with('status', 'Tenant deleted successfully!');
    }
}
