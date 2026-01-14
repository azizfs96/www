@extends('layouts.waf')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>🔐 URL Rules</h3>
        <a href="/waf/url-rules/create" class="btn btn-primary">➕ إضافة قاعدة</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>المسار</th>
                <th>IPs المسموحة</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rules as $rule)
            <tr>
                <td>{{ $rule->name ?? '-' }}</td>
                <td><code>{{ $rule->path }}</code></td>
                <td>{{ $rule->allowed_ips }}</td>
                <td>
                    @if($rule->enabled)
                        <span class="badge bg-success">مفعّلة</span>
                    @else
                        <span class="badge bg-secondary">موقوفة</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
