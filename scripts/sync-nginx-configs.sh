#!/bin/bash
# Script لنسخ ملفات Nginx من storage إلى /etc/nginx/sites-enabled
# يجب تشغيله كـ root أو مع sudo بدون كلمة مرور

STORAGE_DIR="/var/www/waf-dashboard/storage/app/nginx"
NGINX_DIR="/etc/nginx/sites-enabled"

# إنشاء المجلد إذا لم يكن موجوداً
mkdir -p "$STORAGE_DIR"

# نسخ جميع ملفات .waf.conf من storage إلى nginx
if [ -d "$STORAGE_DIR" ]; then
    for file in "$STORAGE_DIR"/*.waf.conf; do
        if [ -f "$file" ]; then
            filename=$(basename "$file")
            target="$NGINX_DIR/$filename"
            cp "$file" "$target"
            chmod 644 "$target"
            chown root:root "$target"
            echo "Copied: $filename"
        fi
    done
fi

# إعادة تحميل Nginx
systemctl reload nginx

