# إنشاء حساب Super Admin

## الطريقة 1: استخدام Tinker (الأسهل)

```bash
php artisan tinker
```

ثم انسخ والصق هذا الكود:

```php
\App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@waf.local',
    'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
    'role' => 'super_admin',
]);
```

أو إذا كان الحساب موجوداً وتريد تحديثه:

```php
$admin = \App\Models\User::firstOrCreate(
    ['email' => 'admin@waf.local'],
    [
        'name' => 'Super Admin',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        'role' => 'super_admin',
    ]
);

if (!$admin->wasRecentlyCreated) {
    $admin->update([
        'name' => 'Super Admin',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        'role' => 'super_admin',
    ]);
    echo "✅ تم تحديث حساب الادمن\n";
} else {
    echo "✅ تم إنشاء حساب الادمن\n";
}
```

## الطريقة 2: استخدام Artisan Command

```bash
php artisan waf:create-admin
```

أو مع تخصيص البيانات:

```bash
php artisan waf:create-admin --email=admin@example.com --password=your-password
```

## الطريقة 3: استخدام Seeder

```bash
php artisan db:seed --class=AdminUserSeeder
```

## بيانات تسجيل الدخول الافتراضية

- **Email**: `admin@waf.local`
- **Password**: `admin123`
- **Role**: `super_admin`

