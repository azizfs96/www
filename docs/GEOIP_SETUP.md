# إعداد قاعدة بيانات GeoIP لـ ModSecurity

## المشكلة
ModSecurity يحتاج قاعدة بيانات GeoIP محلية للتحقق من الدولة. بدونها، قواعد `@geoLookup` و `GEO:COUNTRY_CODE` لن تعمل.

## الحل: تثبيت قاعدة بيانات GeoIP

### الخطوة 1: تثبيت المكتبات المطلوبة

```bash
sudo apt-get update
sudo apt-get install -y libmaxminddb0 libmaxminddb-dev mmdb-bin
```

### الخطوة 2: الحصول على قاعدة بيانات GeoLite2

#### الطريقة 1: تحميل مباشر (سريع)
```bash
# تحميل قاعدة بيانات GeoLite2 باستخدام Account ID و License Key
# احصل على المفاتيح من: https://www.maxmind.com/en/accounts/current/license-key
wget "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key=YOUR_LICENSE_KEY&suffix=tar.gz" -O GeoLite2-Country.tar.gz

# استخراج الملف
tar -xzf GeoLite2-Country.tar.gz

# نسخ قاعدة البيانات
sudo mkdir -p /usr/share/GeoIP
sudo cp GeoLite2-Country_*/GeoLite2-Country.mmdb /usr/share/GeoIP/
sudo chmod 644 /usr/share/GeoIP/GeoLite2-Country.mmdb
```

#### الطريقة 2: استخدام GeoIPUpdate (موصى به للتحديثات التلقائية)
```bash
# تثبيت geoipupdate
sudo apt-get install -y geoipupdate

# تعديل الإعدادات
sudo nano /etc/GeoIP.conf
```

أضف في الملف:
```
AccountID YOUR_ACCOUNT_ID
LicenseKey YOUR_LICENSE_KEY
EditionIDs GeoLite2-Country
```

ثم قم بالتحميل:
```bash
sudo geoipupdate

# قاعدة البيانات ستكون في:
# /usr/share/GeoIP/GeoLite2-Country.mmdb
```

### الخطوة 3: التحقق من قاعدة البيانات

```bash
# التحقق من وجود قاعدة البيانات
ls -lh /usr/share/GeoIP/GeoLite2-Country.mmdb

# اختبار قاعدة البيانات
mmdblookup --file /usr/share/GeoIP/GeoLite2-Country.mmdb --ip 8.8.8.8 country iso_code

# يجب أن ترى: "US" (أو كود الدولة)
```

### الخطوة 4: تكوين ModSecurity لاستخدام قاعدة البيانات

#### أ) تثبيت ModSecurity مع دعم GeoIP

```bash
# التحقق من أن ModSecurity مثبت مع دعم GeoIP
nginx -V 2>&1 | grep -o with-http_geoip_module
```

إذا لم يكن مثبتاً، يجب إعادة تجميع Nginx مع دعم GeoIP.

#### ب) تكوين ModSecurity

أضف في ملف `/etc/modsecurity/modsecurity.conf`:

```apache
# GeoIP Database
SecGeoLookupDb /usr/share/GeoIP/GeoLite2-Country.mmdb
```

### الخطوة 5: اختبار الإعداد

```bash
# اختبار قاعدة البيانات
mmdblookup --file /usr/share/GeoIP/GeoLite2-Country.mmdb --ip 8.8.8.8

# يجب أن ترى:
# {
#   "country": {
#     "iso_code": "US"
#   }
# }
```

### الخطوة 6: إعادة تحميل Nginx

```bash
# اختبار الإعدادات
sudo nginx -t

# إعادة تحميل Nginx
sudo systemctl reload nginx
```

## التحقق من أن القواعد تعمل

بعد إضافة قاعدة حظر دولة من لوحة التحكم، اختبر من IP من تلك الدولة:

```bash
# من سيرفر آخر أو من خلال VPN
curl -I http://your-server-ip/
```

يجب أن تحصل على `403 Forbidden`.

## تحديث قاعدة البيانات تلقائياً

للتحديث التلقائي، أضف في crontab:

```bash
sudo crontab -e
```

أضف:
```
0 2 * * 0 /usr/bin/geoipupdate
```

هذا سيقوم بتحديث قاعدة البيانات كل أسبوع في الساعة 2 صباحاً.

## ملاحظات مهمة

1. **MaxMind Account**: تحتاج حساب في MaxMind للحصول على مفتاح الترخيص
   - سجل في: https://www.maxmind.com/en/accounts/current/license-key
   - احصل على License Key مجاني

2. **البديل**: يمكن استخدام قاعدة بيانات GeoIP مجانية من مصادر أخرى، لكن GeoLite2 من MaxMind هو الأفضل

3. **الأداء**: قاعدة البيانات المحلية أسرع بكثير من API calls

## استكشاف الأخطاء

### المشكلة: `@geoLookup` لا يعمل
- تأكد من أن قاعدة البيانات موجودة في المسار الصحيح
- تأكد من صلاحيات القراءة على الملف
- تحقق من logs: `sudo tail -f /var/log/nginx/error.log`

### المشكلة: قاعدة البيانات قديمة
```bash
sudo geoipupdate
sudo systemctl reload nginx
```
