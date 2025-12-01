# flash-sale-checkout-task 🚀

**كاتب المشروع:** Karim Ibrahiem  
**وصف موجز:** نظام API بسيط لـ Flash-Sale Checkout يدعم: حجز مؤقت (hold) لمنع oversell، إنشاء أوامر (orders)، معالجة ويبهوك الدفع idempotent، ومسح الحجز المنتهي عبر scheduler.  

---

## محتوى هذا الملف
1. لمحة عامة وفرضيات العمل  
2. إعداد المشروع (التثبيت، .env، تشغيل السيرفر)  
3. المتغيرات التي يجب وضعها في `.env` (بما فيها مفاتيح PayMob)  
4. كيفية تشغيل المايجريشنز لقاعدة البيانات والـ testing DB  
5. كيفية تشغيل الـ scheduler/cron للـ ClearExpireHolds  
6. الـ API endpoints (قائمة + أمثلة طلبات)
7. ملف الpostman collection في رووت المشروع 
8. flash-sale-collection.postman_collection.json 
10. إرشادات تشغيل الاختبارات الآلية وشرح ماذا تختبر كل اختبار  
11. ملاحظات نهائية وملحوظات أمنية

---

## 1 — لمحة عامة وفرضيات العمل
المشروع يبني API-only back-end على Laravel 12 مع MySQL أو SQLite للـ testing.  
الفكرة الأساسية:
- نحتفظ بـ `product.stock` كالموجود فعلاً.
- نستخدم `product.reserved_stock` لحجز الكمية (holds).
- Hold له `expires_at` قصير (مثلاً 2-5 دقائق) ويمنع الآخرين من الحجز.
- Job منظّمة تنظف الحجز المنتهي وتعيد تقليل `reserved_stock`.
- Webhook معالجة الدفع آمنة من التكرار (idempotency) ومن وصول الرسائل قبل إنشاء الطلب.

**افتراضات مهمة:**
- كل Hold مرتبط بمستخدم واحد (user_id).
- كل Hold يمكن استخدامه مرة واحدة فقط لتحويله إلى Order.
- Webhook من PayMob يرسل حقل `idempotency_key` و `status` و `order_id` أو بيانات مشابهة (راجع `.env` و trait PayMobTrait).

---

## 2 — تثبيت المشروع (خطوات دقيقة)

افتح الطرفية داخل مجلد المشروع ثم:

1. استنساخ المستودع (لو لم تفعل):
```bash
git clone https://github.com/kariemibrahiem/flash-sale-checkout-task.git
cd flash-sale-checkout-task

2 — تثبيت الاعتمادات الخاصة بالمشروع
composer install
3 — إنشاء ملف ال .env
cp .env.example .env
4 — توليد project key
php artisan key:generate
5 — إعداد متغيّرات البيئة (ENV)
و دي كريدينشيال الpaymob للتيست 
PAYMOB_INTEGRATION_ID=5269504
PAYMOB_API_KEY=ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5LmV5SmpiR0Z6Y3lJNklrMWxjbU5vWVc1MElpd2ljSEp2Wm1sc1pWOXdheUk2TVRBM05EazBOU3dpYm1GdFpTSTZJakUzTlRjek1qUTNOekl1TmpJNE9UazRJbjAuazhKT3pSejRYTjV4VFZIZHgxQTVLcUQySDBEelpJdkJnYld0akM0WU5QNE1TSXBBQjJyMnE4RmVfb0VmY2FkS1FERE1SMUFvWWh1UWFJNEpXUHNkMlE=
PAYMOB_MERCHANT_ID=1074945
PAYMOB_HMAC=4EC2BF5BE9CF03F72FDDB9E4F50A7EFE
انشاء المايجريشن للمشروع
CREATE DATABASE getPayIn
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
php artisan migrate
7 — إنشاء المستخدم الافتراضي
email = getPayIn@gmail.com
pass  = getPayIn
8 — ربط الـ Storage (not very required for out project)
php artisan sotrage:link
9 — تشغيل السيرفر
php artisan serve
10 — تشغيل قاعدة البيانات الخاصة بالاختبارات (Testing DB)
cp .env .env.testing
إنشئ DB جديدة باسم:
CREATE DATABASE flash_sale_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
php artisan migrate --env=testing
11 — تشغيل الاختبارات
php artisan test
ماذا تغطي الاختبارات؟
- التأكد من إنشاء hold بدون oversell
- التأكد من انتهاء hold وإعادة المخزون
- التأكد من idempotency في الـ webhook
- التأكد من إنشاء الطلب بشكل صحيح

12 — تشغيل الـ Scheduler لمسح الـ Holds المنتهية
- في الproductsion يتم استخدام cronJob
php artisan schedule:work

15 — Flow العمل الكامل

المستخدم يطلب Hold للمنتج
- النظام يحجز الكمية في reserved_stock
- المستخدم يذهب لصفحة الدفع عبر PayMob
- PayMob يستدعي الـ webhook عند الدفع
- النظام يحدث حالة الطلب (paid/cancelled)
- job scheduler ينظف holds المنتهية

- ال postman collection موجود في المشروع 
