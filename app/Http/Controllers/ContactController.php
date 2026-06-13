<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * استقبال طلب التواصل من صفحة الهبوط (عام، بدون تسجيل دخول)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'message' => 'nullable|string|max:5000',
        ]);

        $data['ip']         = $request->ip();
        $data['user_agent'] = substr((string) $request->userAgent(), 0, 255);
        $data['status']     = 'new';

        ContactRequest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! We will contact you soon.',
        ]);
    }

    /**
     * عرض طلبات التواصل (للمشرف فقط)
     */
    public function index(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $requests = ContactRequest::orderByDesc('created_at')->paginate(25);
        $newCount = ContactRequest::where('status', 'new')->count();

        return view('waf.leads.index', compact('requests', 'newCount'));
    }

    /**
     * تحديث حالة طلب
     */
    public function updateStatus(Request $request, ContactRequest $contactRequest)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate(['status' => 'required|in:new,contacted,closed']);
        $contactRequest->update(['status' => $request->status]);

        return back()->with('status', 'Request status updated.');
    }
}
