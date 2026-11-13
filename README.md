# سیستم توزیع و سرور به‌روزرسانی هوشمند افزونه‌های وردپرس

این مخزن به عنوان مخزن مرکزی و سرور توزیع دوکاناله (**آزمایشی / Staging** و **محصول نهایی / Production**) برای افزونه‌ها و قالب‌های پچ‌شده و شخصی‌سازی‌شده طراحی و ساختاربندی شده است.

---

## ۱. ساختار معماری و دسته‌بندی فایل‌ها (`plugins/raw/`)

سورس فایل‌های خام، دسته‌بندی‌شده و نسخه‌های بازنویسی‌شده در مسیر `plugins/raw/` طبق ۳ دسته اصلی مدیریت می‌شوند:

```text
plugins/raw/
├── on/                                     <-- افزونه‌های دارای قابلیت و لینک آپدیتر (Updater) ارائه‌دهنده
│   └── old/                                <-- نسخه‌های اصلی و دست‌نخورده ارائه‌دهنده (منبع استخراج لینک‌های دانلود)
│       ├── advanced-custom-fields-pro/
│       ├── elementor-pro/
│       ├── jet-compare-wishlist/
│       ├── jet-elements/
│       ├── jet-engine/
│       ├── jet-search/
│       ├── jet-smart-filters/
│       ├── jet-tabs/
│       ├── jet-woo-builder/
│       ├── powerpack-elements/
│       └── wp-rocket/
│
├── off/                                    <-- افزونه‌های فاقد سیستم یا لینک آپدیتر ارائه‌دهنده
│   ├── all-in-one-wp-migration-unlimited-extension-2.86-rewrite/
│   ├── ithemes-security-pro/
│   ├── jet-blocks-1.3.12-rewrite/
│   ├── sitepress-multilingual-cms/
│   ├── wpml-media-translation/
│   └── wpml-string-translation/
│
└── side/                                   <-- قالب‌ها، تم‌های فرزند و افزونه‌های کمکی و وابسته جانبی (فاقد آپدیتر)
    ├── hello-elementor/                    <-- قالب والد سبک رسمی المنتور
    ├── hello-child/                        <-- چایلد تم اختصاصی المنتور
    ├── jet-elements-dynamic-data-master/   <-- افزونه داده‌های پویا برای JetElements
    ├── jet-engine-custom-visibility-conditions/ <-- افزونه شرایط نمایش سفارشی JetEngine
    └── jet-offcanvas-column-main/          <-- افزونه ستون‌های آف‌کانواس المنتور
```

> ⚠️ **قانون کلیدی ماندگاری (Preservation Rule):**  
> تمامی پوشه‌ها و فایل‌های موجود در `plugins/raw/on/old/` به عنوان منبع مرجع نگهداری می‌شوند. این فایل‌ها حاوی آدرس‌های Endpoint ارائه‌دهنده اصلی (نظیر سرور `esdownload.ir`) هستند و برای بررسی و دریافت آخرین نسخه‌های روز نباید حذف یا تغییر نام داده شوند.

> ℹ️ **وضعیت پوشه `side`:**  
> تمام موارد موجود در پوشه `side` بررسی شدند؛ هیچ‌کدام دارای لینک آپدیتر یا بررسی‌کننده بیرونی نیستند و کاملاً به عنوان قالب یا ماژول کمکی مستقل عمل می‌کنند.

---

## ۲. ساختار کانال‌های انتشار و سرور به‌روزرسانی (`plugins/updates/`)

بسته‌های فشرده نهایی و فایل‌های پیکربندی JSON جهت اتصال خودکار وردپرس به این سرور از طریق کتابخانه **Plugin Update Checker (PUC)** در مسیر `plugins/updates/` قرار دارند:

```text
plugins/updates/
├── up-on/                                  <-- کانال‌های توزیع و آپدیت افزونه‌های متصل به آپدیتر
│   ├── staging/                            <-- محیط آزمایشی (Staging) جهت تست و بررسی اولیه
│   │   ├── jet-elements/
│   │   │   ├── info.json                   <-- فایل متادیتای آپدیت محیط تست
│   │   │   └── jet-elements-v2.9.2.8.zip   <-- فایل فشرده با نام استاندارد جهت تست
│   │   ├── jet-smart-filters/
│   │   │   ├── info.json                   <-- فایل متادیتای آپدیت محیط تست
│   │   │   └── jet-smart-filters-v3.8.6.3.zip <-- فایل فشرده با نام استاندارد جهت تست
│   │   ├── jet-search/
│   │   │   ├── info.json                   <-- فایل متادیتای آپدیت محیط تست
│   │   │   └── jet-search-v3.6.3.1.zip     <-- فایل فشرده با نام استاندارد جهت تست
│   │   ├── jet-compare-wishlist/
│   │   │   ├── info.json                   <-- فایل متادیتای آپدیت محیط تست
│   │   │   └── jet-compare-wishlist-v1.5.13.zip <-- فایل فشرده با نام استاندارد جهت تست
│   │   ├── jet-woo-builder/
│   │   │   ├── info.json                   <-- فایل متادیتای آپدیت محیط تست
│   │   │   └── jet-woo-builder-v2.3.6.zip  <-- فایل فشرده با نام استاندارد جهت تست
│   │   ├── jet-blocks/
│   │   │   ├── info.json                   <-- فایل متادیتای آپدیت محیط تست
│   │   │   └── jet-blocks-v1.3.12.zip      <-- فایل فشرده با نام استاندارد جهت تست
│   │   └── all-in-one-wp-migration-unlimited-extension/
│   │       ├── info.json
│   │       └── all-in-one-wp-migration-unlimited-extension-v2.86.zip
│   │
│   └── production/                         <-- محیط محصول پایدار (Production) جهت تحویل نهایی به کاربران (تنها پس از تست و تأیید در Staging)
│       ├── jet-blocks/
│       │   ├── info.json                   <-- فایل متادیتای آپدیت محیط پروداکشن
│       │   └── jet-blocks-v1.3.12-[کد_محرمانه_عددی].zip <-- فایل فشرده محافظت‌شده با پسوند عددی اختصاصی
│       └── all-in-one-wp-migration-unlimited-extension/
│           ├── info.json
│           └── all-in-one-wp-migration-unlimited-extension-v2.86-[کد_محرمانه_عددی].zip
│
├── up-old/                                 <-- کانال بسته‌ها و آپدیت‌های آرشیوی / نسخه‌های مرجع
│   ├── staging/
│   │   ├── jet-elements/
│   │   │   └── jet-elements-v2.6.13.1.zip  <-- آرشیو نسخه قبلی جهت بازگشت یا استفاده مجدد
│   │   ├── jet-smart-filters/
│   │   │   └── jet-smart-filters-v3.3.2.zip <-- آرشیو نسخه قبلی جهت بازگشت یا استفاده مجدد
│   │   ├── jet-search/
│   │   │   └── jet-search-v3.1.1.zip       <-- آرشیو نسخه قبلی جهت بازگشت یا استفاده مجدد
│   │   ├── jet-compare-wishlist/
│   │   │   └── jet-compare-wishlist-v1.5.5.2.zip <-- آرشیو نسخه قبلی جهت بازگشت یا استفاده مجدد
│   │   └── jet-woo-builder/
│   │       └── jet-woo-builder-v2.1.7.3.zip <-- آرشیو نسخه قبلی جهت بازگشت یا استفاده مجدد
│   └── production/
│
└── up-side/                                <-- کانال بسته‌های قالب‌ها و افزونه‌های جانبی
    ├── staging/
    └── production/
```

> 🔒 **استاندارد نام‌گذاری بسته‌های پروداکشن (Production Security Naming):**  
> تمامی بسته‌های ارائه‌شده در کانال پروداکشن پس از نام و نسخه، یک خط تیره (`-`) و یک دنباله عددی نامنظم و یکتا (مانند `-4829501832`) دریافت می‌کنند تا آدرس مستقیم فایل فشرده قابل حدس زدن نباشد و تنها از طریق متادیتای مجاز سرور قابل دریافت باشد.

---

## ۳. لیست افزونه‌های فعال و آدرس‌های اندپوینت (Endpoints)

### افزونه JetWooBuilder (نسخه 2.3.6)

* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-woo-builder/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-woo-builder/jet-woo-builder-v2.3.6.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **آرشیو نسخه قبلی (up-old):**
  * لینک دانلود نسخه 2.1.7.3: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-old/staging/jet-woo-builder/jet-woo-builder-v2.1.7.3.zip`

### افزونه JetCompareWishlist (نسخه 1.5.13)
* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-compare-wishlist/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-compare-wishlist/jet-compare-wishlist-v1.5.13.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **آرشیو نسخه قبلی (up-old):**
  * لینک دانلود نسخه 1.5.5.2: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-old/staging/jet-compare-wishlist/jet-compare-wishlist-v1.5.5.2.zip`

### افزونه JetSearch (نسخه 3.6.3.1)
* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-search/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-search/jet-search-v3.6.3.1.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **آرشیو نسخه قبلی (up-old):**
  * لینک دانلود نسخه 3.1.1: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-old/staging/jet-search/jet-search-v3.1.1.zip`

### افزونه JetSmartFilters (نسخه 3.8.6.3)
* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-smart-filters/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-smart-filters/jet-smart-filters-v3.8.6.3.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **آرشیو نسخه قبلی (up-old):**
  * لینک دانلود نسخه 3.3.2: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-old/staging/jet-smart-filters/jet-smart-filters-v3.3.2.zip`

### افزونه JetElements For Elementor (نسخه 2.9.2.8)
* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-elements/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-elements/jet-elements-v2.9.2.8.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **آرشیو نسخه قبلی (up-old):**
  * لینک دانلود نسخه 2.6.13.1: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-old/staging/jet-elements/jet-elements-v2.6.13.1.zip`

### افزونه JetBlocks For Elementor (نسخه 1.3.12)
* **کانال Staging:**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-blocks/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-blocks/jet-blocks-v1.3.12.zip`
* **کانال Production (لینک محافظت‌شده عددی):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/production/jet-blocks/info.json`
  * لینک دانلود مستقیم: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/production/jet-blocks/jet-blocks-v1.3.12-4829501832.zip`

### افزونه All-in-One WP Migration Unlimited Extension (نسخه 2.86)
* **کانال Staging:**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/all-in-one-wp-migration-unlimited-extension/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/all-in-one-wp-migration-unlimited-extension/all-in-one-wp-migration-unlimited-extension-v2.86.zip`
* **کانال Production (لینک محافظت‌شده عددی):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/production/all-in-one-wp-migration-unlimited-extension/info.json`
  * لینک دانلود مستقیم: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/production/all-in-one-wp-migration-unlimited-extension/all-in-one-wp-migration-unlimited-extension-v2.86-7193850241.zip`
