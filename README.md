# سیستم توزیع و چرخه به‌روزرسانی هوشمند افزونه‌ها

این مخزن طبق چرخه اختصاصی و استاندارد مدیریت افزونه‌ها ساختاربندی شده است:

## ساختار پوشه‌ها:

```text
plugins/
├── raw/
│   └── [plugin-name]-[version]-rewrite/     <-- افزونه پس از بررسی، پاکسازی و بازنویسی (کدهای دستکاری‌شده رینیم و اصلاح می‌شوند)
│
└── updates/
    └── [plugin-name]/
        ├── staging/                         <-- کانال تست (شامل ZIP پچ‌شده و info.json تستی)
        └── production/                      <-- کانال انتشار عمومی (شامل ZIP پچ‌شده و info.json نهایی خریداران)
```

## چرخه کاری:
1. قرار دادن پوشه نسخه جدید در `plugins/raw/[plugin-name]-[version]/`
2. بررسی و پاکسازی کدها و تغییر نام پوشه به `[plugin-name]-[version]-rewrite/`
3. ساخت پکیج و قرارگیری در `plugins/updates/[plugin-name]/staging/` جهت تست
4. پس از تایید تست، انتقال به `plugins/updates/[plugin-name]/production/` جهت دریافت توسط مشتریان
