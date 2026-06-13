@extends('layouts.waf')

@section('title', 'Consultation Requests')

@section('styles')
@include('waf.soc._theme')
<style>
    .ld-alert { display:flex; align-items:center; gap:10px; border-radius:10px; padding:12px 14px; margin-bottom:16px; font-size:13.5px; border:1px solid rgba(74,222,128,.32); background:rgba(74,222,128,.08); color:#86efac; }
    .ld-msg { color:#cbd5e1; font-size:13px; max-width:380px; white-space:pre-wrap; }
    .ld-contact { color:#e5e7eb; font-weight:600; }
    .ld-sub { color:#9ca3af; font-size:12px; font-family:ui-monospace,monospace; }
    .ld-status-form { display:inline-flex; gap:6px; align-items:center; }
    .ld-status-form select { background:#0b0b0d; border:1px solid rgba(255,255,255,.16); border-radius:7px; color:#e5e7eb; font-size:12px; padding:5px 8px; }
    .ld-status-form button { background:rgba(168,85,247,.16); border:1px solid rgba(168,85,247,.4); color:#e9d5ff; border-radius:7px; font-size:12px; padding:5px 10px; cursor:pointer; }
    .ld-pill-new { color:#fbbf24; border-color:rgba(251,191,36,.35); background:rgba(251,191,36,.12); }
    .ld-pill-contacted { color:#60a5fa; border-color:rgba(96,165,250,.35); background:rgba(96,165,250,.12); }
    .ld-pill-closed { color:#9ca3af; border-color:rgba(255,255,255,.2); background:rgba(255,255,255,.05); }
</style>
@endsection

@section('content')
<div class="soc-page-head">
    <div>
        <h1 class="soc-page-title">Consultation Requests</h1>
        <div class="soc-page-subtitle">Leads submitted through the public contact form on the landing page.</div>
    </div>
    <span class="soc-chip">{{ $newCount }} New</span>
</div>

@if (session('status'))
    <div class="ld-alert">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

<div class="soc-panel">
    <div class="soc-panel-head">
        <div>
            <div class="soc-panel-title">All Requests</div>
            <div class="soc-panel-sub">Newest first</div>
        </div>
        <span class="soc-pill soc-open">{{ $requests->total() }} total</span>
    </div>
    <div class="soc-panel-body soc-table-wrap">
        <table class="soc-table">
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($requests as $r)
                <tr>
                    <td>
                        <div class="ld-contact">{{ $r->name }}</div>
                        <div class="ld-sub">{{ $r->email }}</div>
                        @if($r->phone)<div class="ld-sub">{{ $r->phone }}</div>@endif
                    </td>
                    <td><div class="ld-msg">{{ $r->message ?: '—' }}</div></td>
                    <td>
                        <span class="soc-pill ld-pill-{{ $r->status }}">{{ ucfirst($r->status) }}</span>
                    </td>
                    <td class="ld-sub">{{ $r->created_at->format('Y-m-d H:i') }}</td>
                    <td style="text-align:right;">
                        <form method="POST" action="{{ route('leads.status', $r) }}" class="ld-status-form">
                            @csrf @method('PATCH')
                            <select name="status">
                                <option value="new" {{ $r->status==='new'?'selected':'' }}>New</option>
                                <option value="contacted" {{ $r->status==='contacted'?'selected':'' }}>Contacted</option>
                                <option value="closed" {{ $r->status==='closed'?'selected':'' }}>Closed</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;padding:40px;color:#9ca3af;">No consultation requests yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="soc-panel-body">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
