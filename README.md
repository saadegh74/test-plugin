# سیستم توزیع و سرور به‌روزرسانی هوشمند وردپرس (برند تست)

این مخزن به عنوان مخزن مرکزی و سرور توزیع دوکاناله (**آزمایشی / Staging** و **محصول نهایی / Production**) برای افزونه‌ها و قالب‌های پچ‌شده و شخصی‌سازی‌شده تحت نام تجاری **برند تست** طراحی و ساختاربندی شده است.

---

## ۱. ساختار معماری و دسته‌بندی فایل‌ها (`plugins/raw/`)

سورس فایل‌های خام، دسته‌بندی‌شده و نسخه‌های بازنویسی‌شده در مسیر `plugins/raw/` طبق ۳ دسته اصلی مدیریت می‌شوند:

```text
plugins/raw/
├── on/                                     <-- افزونه‌های دارای قابلیت و لینک آپدیتر (Updater)
│   ├── old/                                <-- نسخه‌های اصلی و دست‌نخورده ارائه‌دهنده (منبع استخراج لینک‌های دانلود)
│   │   ├── advanced-custom-fields-pro/
│   │   ├── elementor-pro/
│   │   ├── jet-compare-wishlist/
│   │   ├── jet-elements/
│   │   ├── jet-engine/
│   │   ├── jet-search/
│   │   ├── jet-smart-filters/
│   │   ├── jet-tabs/
│   │   ├── jet-woo-builder/
│   │   ├── powerpack-elements/
│   │   └── wp-rocket/
│   │
│   ├── all-in-one-wp-migration-unlimited-extension-2.86-rewrite/   <-- نسخه بازنویسی‌شده و متصل به سرور اختصاصی
│   └── jet-blocks-1.3.12-rewrite/                                  <-- نسخه بازنویسی‌شده، رفع خطای ۴۰۳ و ترجمه اختصاصی
│
├── off/                                    <-- افزونه‌های مستقل بدون سیستم آپدیتر (فاقد Updater اختصاصی)
│   ├── sitepress-multilingual-cms/
│   ├── wpml-media-translation/
│   └── wpml-string-translation/
│
└── side/                                   <-- قالب‌ها، تم‌های فرزند و افزونه‌های کمکی و وابسته جانبی
    ├── hello-elementor/                    <-- قالب والد سبک رسمی المنتور
    ├── hello-child/                        <-- چایلد تم اختصاصی المنتور
    ├── jet-elements-dynamic-data-master/   <-- افزونه داده‌های پویا برای JetElements
    ├── jet-engine-custom-visibility-conditions/ <-- افزونه شرایط نمایش سفارشی JetEngine
    └── jet-offcanvas-column-main/          <-- افزونه ستون‌های آف‌کانواس المنتور
```

> ⚠️ **قانون کلیدی ماندگاری (Preservation Rule):**  
> تمامی پوشه‌ها و فایل‌های موجود در `plugins/raw/on/old/` به عنوان منبع مرجع نگهداری می‌شوند. این فایل‌ها حاوی آدرس‌های Endpoint ارائه‌دهنده اصلی (نظیر سرور `esdownload.ir`) هستند و برای بررسی و دریافت آخرین نسخه‌های روز نباید حذف یا تغییر نام داده شوند.

---

## ۲. ساختار کانال‌های انتشار و سرور به‌روزرسانی (`plugins/updates/`)

بسته‌های فشرده نهایی و فایل‌های پیکربندی JSON جهت اتصال خودکار وردپرس به این سرور از طریق کتابخانه **Plugin Update Checker (PUC)** در مسیر `plugins/updates/` قرار دارند:

```text
plugins/updates/
├── up-on/                                  <-- کانال‌های توزیع و آپدیت افزونه‌های متصل به آپدیتر
│   ├── staging/                            <-- محیط آزمایشی (Staging) جهت تست و بررسی اولیه
│   │   ├── jet-blocks/
│   │   │   ├── info.json                   <-- فایل متادیتای آپدیت محیط تست
│   │   │   └── jet-blocks-v1.3.12.zip      <-- فایل فشرده آماده نصب
│   │   └── all-in-one-wp-migration-unlimited-extension/
│   │       ├── info.json
│   │       └── all-in-one-wp-migration-unlimited-extension-v2.86.zip
│   │
│   └── production/                         <-- محیط محصول پایدار (Production) جهت تحویل نهایی به کاربران
│       ├── jet-blocks/
│       │   ├── info.json                   <-- فایل متادیتای آپدیت محیط پروداکشن
│       │   └── jet-blocks-v1.3.12.zip
│       └── all-in-one-wp-migration-unlimited-extension/
│           ├── info.json
│           └── all-in-one-wp-migration-unlimited-extension-v2.86.zip
│
├── up-old/                                 <-- کانال بسته‌ها و آپدیت‌های آرشیوی / نسخه‌های مرجع
│   ├── staging/
│   └── production/
│
└── up-side/                                <-- کانال بسته‌های قالب‌ها و افزونه‌های جانبی
    ├── staging/
    └── production/
```

---

## ۳. چرخه کاری و روند توسعه (Workflow)

1. **دریافت و استخراج فایل‌ها:**  
   بسته‌های جدید پس از باز شدن، بررسی شده و در صورت داشتن آپدیتر در `plugins/raw/on/old/`، در صورت عدم نیاز در `plugins/raw/off/` و در صورت قالب یا اکستنشن کمکی بودن در `plugins/raw/side/` ذخیره می‌شوند.
2. **پچ و استانداردسازی (Rebranding & Patching):**  
   - تغییر متادیتا و برندینگ به **برند تست**.  
   - پاکسازی فایل‌های مخفی توسعه‌دهنده (`gulpfile.js`, `package.json`, `.github`, `.gitignore`, `dummy-data`) جهت جلوگیری قطعی از خطای **403 Forbidden** و مسدودسازی توسط فایروال سرورها (ModSecurity, LiteSpeed, Imunify360).  
   - تغییر نام پوشه پچ‌شده با فرمت `[plugin-slug]-[version]-rewrite`.
3. **تزریق کتابخانه PUC (Plugin Update Checker):**  
   کتابخانه سبک و بهینه در پوشه افزونه مستقر شده و آدرس Endpoint مخزن گیت‌هاب در فایل اصلی PHP افزونه ست می‌شود.
4. **انتشار در کانال آزمایشی (Staging):**  
   ساخت پکیج ZIP استاندارد و تنظیم `info.json` در `plugins/updates/up-on/staging/[plugin-slug]/` جهت تست نصب و دریافت اعلان آپدیت در محیط دمو.
5. **انتشار در کانال محصول نهایی (Production):**  
   انتقال یا همگام‌سازی بسته با `plugins/updates/up-on/production/[plugin-slug]/` جهت استفاده نهایی کاربران.

---

## ۴. لیست افزونه‌های فعال و آدرس‌های اندپوینت (Endpoints)

### افزونه JetBlocks For Elementor (نسخه 1.3.12)
- **کانال Staging:**
  - اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-blocks/info.json`
  - لینک دانلود مستقیم: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-blocks/jet-blocks-v1.3.12.zip`
- **کانال Production:**
  - اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/production/jet-blocks/info.json`
  - لینک دانلود مستقیم: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/production/jet-blocks/jet-blocks-v1.3.12.zip`

### افزونه All-in-One WP Migration Unlimited Extension (نسخه 2.86)
- **کانال Staging:**
  - اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/all-in-one-wp-migration-unlimited-extension/info.json`
  - لینک دانلود مستقیم: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/all-in-one-wp-migration-unlimited-extension/all-in-one-wp-migration-unlimited-extension-v2.86.zip`
- **کانال Production:**
  - اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/production/all-in-one-wp-migration-unlimited-extension/info.json`
  - لینک دانلود مستقیم: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/production/all-in-one-wp-migration-unlimited-extension/all-in-one-wp-migration-unlimited-extension-v2.86.zip`
