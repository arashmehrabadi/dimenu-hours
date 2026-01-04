# dimenu-hours — Context

هدف افزونه: مدیریت ساعت کاری + استثناها + نمایش وضعیت باز/بسته در منو، و مهم‌تر از همه:
- منو همیشه قابل مشاهده باشد
- خارج از ساعات کاری، کاربر نتواند سفارش ثبت کند (Add to cart / cart / checkout gated)

وضعیت فعلی پروژه (تا امروز):
- اسکلت افزونه ساخته شده
- صفحه تنظیمات ساده فعال است (enabled/manual_closed/timezone/messages)
- Status Engine ساخته شده و از REST خروجی می‌دهد
- گیت فرانت (JS) برای disable کردن دکمه‌های سفارش + بنر وضعیت فعال شده
- گیت سروری WooCommerce پیاده شده:
  - Add to cart server-side block
  - Redirect از cart/checkout (با تشخیص robust و اجرای زودهنگام)
  - Block checkout به‌عنوان لایه اضافه

URL تست REST:
- /wp-json/dimenu/v1/status

نکته مهم:
- فعلاً Exceptions هنوز وارد Status Engine نشده‌اند (گام بعدی)
- فعلاً UI برنامه هفتگی/استثناها در پنل ادمین کامل نشده (گام بعدی)
