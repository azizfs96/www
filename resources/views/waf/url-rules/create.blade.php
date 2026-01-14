@extends('layouts.waf')

@section('content')
    <h3>➕ إضافة قاعدة URL جديدة</h3>

    <form action="/waf/url-rules" method="post" class="mt-4">
        @csrf

        <div class="mb-3">
            <label class="form-label">الاسم (اختياري)</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">المسار (URL Path)</label>
            <input type="text" name="path" class="form-control" placeholder="/admin" value="{{ old('path') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">الـ IPs المسموحة (افصل بينها بفاصلة أو سطر جديد)</label>
            <textarea name="allowed_ips" class="form-control" rows="3" placeholder="99.6.12.1, 1.2.3.4">{{ old('allowed_ips') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">حفظ القاعدة</button>
    </form>
@endsection
