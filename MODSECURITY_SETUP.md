# إعداد ModSecurity على السيرفر

## الخطوات المطلوبة على سيرفر Linux

### 1. إنشاء المجلدات المطلوبة

```bash
sudo mkdir -p /etc/nginx/modsec
sudo mkdir -p /etc/nginx/modsec/sites
sudo mkdir -p /tmp/modsec
sudo chown -R www-data:www-data /etc/nginx/modsec
sudo chown -R www-data:www-data /tmp/modsec
sudo chmod 755 /etc/nginx/modsec
```

### 2. إنشاء ملف ModSecurity الأساسي

```bash
sudo nano /etc/nginx/modsec/modsecurity.conf
```

أضف المحتوى التالي:

```nginx
# ModSecurity Base Configuration
SecRuleEngine On
SecRequestBodyAccess On
SecResponseBodyAccess Off
SecRequestBodyLimit 13107200
SecRequestBodyNoFilesLimit 131072
SecRequestBodyInMemoryLimit 131072
SecRequestBodyLimitAction Reject
SecPcreMatchLimit 100000
SecPcreMatchLimitRecursion 100000

# Audit Log
SecAuditEngine RelevantOnly
SecAuditLogRelevantStatus "^(?:5|4(?!04))"
SecAuditLogParts ABIJDEFHZ
SecAuditLogType Serial
SecAuditLog /var/log/modsec_audit.log

# Arguments
SecArgumentSeparator &
SecCookieFormat 0
SecTmpDir /tmp/modsec/
SecDataDir /tmp/modsec/

# Upload handling
SecUploadDir /tmp/
SecUploadKeepFiles Off

# Debug Log (optional - disable in production)
SecDebugLog /var/log/modsec_debug.log
SecDebugLogLevel 0
```

### 3. إنشاء ملف القواعد العامة

```bash
sudo nano /etc/nginx/modsec/global-rules.conf
```

أضف:

```nginx
# Global WAF Rules
# These rules apply to all sites unless overridden

# Block common attacks
SecRule ARGS|REQUEST_HEADERS "@rx (?i:(?:<script|javascript:|onerror=))" \
    "id:100001,phase:2,deny,status:403,msg:'XSS Attack Detected'"

SecRule ARGS|REQUEST_HEADERS "@rx (?i:(?:union.*select|insert.*into|delete.*from))" \
    "id:100002,phase:2,deny,status:403,msg:'SQL Injection Detected'"

# Block suspicious user agents
SecRule REQUEST_HEADERS:User-Agent "@rx (?i:(?:sqlmap|nikto|nmap|masscan))" \
    "id:100003,phase:1,deny,status:403,msg:'Suspicious User Agent'"
```

### 4. إنشاء ملفات IP Rules العامة

```bash
sudo touch /etc/nginx/modsec/global-whitelist.txt
sudo touch /etc/nginx/modsec/global-blacklist.txt
sudo chown www-data:www-data /etc/nginx/modsec/global-*.txt
```

### 5. (اختياري) تثبيت OWASP CRS

إذا كنت تريد استخدام قواعد OWASP ModSecurity Core Rule Set:

```bash
cd /etc/nginx/modsec
sudo git clone https://github.com/coreruleset/coreruleset.git owasp-crs
cd owasp-crs
sudo cp crs-setup.conf.example crs-setup.conf
sudo chown -R www-data:www-data /etc/nginx/modsec/owasp-crs
```

### 6. إعدادات Nginx الرئيسية

تأكد من أن ملف `/etc/nginx/nginx.conf` يحتوي على:

```nginx
http {
    # تفعيل ModSecurity
    modsecurity on;
    
    # باقي الإعدادات...
    include /etc/nginx/sites-enabled/*.conf;
}
```

### 7. منح صلاحيات sudo لـ www-data

```bash
sudo visudo
```

أضف في نهاية الملف:

```
www-data ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload nginx
www-data ALL=(ALL) NOPASSWD: /usr/bin/nginx -t
```

### 8. اختبار Nginx

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## ملاحظات مهمة

1. **السجلات (Logs)**:
   - Audit Log: `/var/log/modsec_audit.log`
   - Debug Log: `/var/log/modsec_debug.log`
   - تأكد من وجود الصلاحيات المناسبة

2. **الأداء**:
   - استخدم `SecAuditEngine RelevantOnly` لتقليل السجلات
   - عطّل `SecDebugLogLevel` في الإنتاج (ضعه 0)

3. **الأمان**:
   - لا تشغّل ModSecurity بمستوى Paranoia 4 مباشرة
   - ابدأ بمستوى 1 واختبر الموقع
   - راقب False Positives

4. **الصيانة**:
   - احذف السجلات القديمة بانتظام
   - حدّث OWASP CRS دورياً
   - راجع القواعد المحظورة

---

## استكشاف الأخطاء

### خطأ: modsecurity.conf not found

```bash
# تحقق من وجود الملف
ls -la /etc/nginx/modsec/modsecurity.conf

# إن لم يكن موجوداً، أنشئه كما في الخطوة 2 أعلاه
```

### خطأ: Permission denied

```bash
sudo chown -R www-data:www-data /etc/nginx/modsec
sudo chmod -R 755 /etc/nginx/modsec
```

### خطأ: Include file not found

```bash
# تحقق من المسارات
sudo find /etc/nginx/modsec -type f

# تأكد من أن Laravel يكتب الملفات بشكل صحيح
sudo tail -f /var/log/nginx/error.log
```

---

## للاختبار على Windows (Development)

على Windows، لن يعمل ModSecurity لأن Nginx على Windows لا يدعمه.
يمكنك:

1. استخدام Docker لتشغيل Nginx + ModSecurity
2. استخدام WSL2 مع Linux
3. اختبار واجهة الموقع فقط (Laravel Web Interface)

الكود سيعمل بدون أخطاء حتى لو لم يكن ModSecurity موجوداً.
