# سیستم توزیع و سرور به‌روزرسانی هوشمند افزونه‌های وردپرس

این مخزن به عنوان مخزن مرکزی و سرور توزیع دوکاناله (**آزمایشی / Staging** و **محصول نهایی / Production**) برای مدیریت، پاک‌سازی و به‌روزرسانی خودکار افزونه‌ها و قالب‌های وردپرس طراحی و ساختاربندی شده است.

---

## ۱. ساختار معماری و دسته‌بندی فایل‌ها (`plugins/raw/`)

سورس فایل‌های خام و نسخه‌های پایه در مسیر `plugins/raw/` طبق ۳ دسته اصلی مدیریت می‌شوند:

```text
plugins/raw/
├── on/                                     <-- افزونه‌های دارای قابلیت و لینک آپدیتر سرور
│   ├── advanced-custom-fields-pro/         <-- نسخه old پاک‌سازی‌شده (6.8.11) متصل به سرور ما
│   ├── jet-compare-wishlist/               <-- نسخه old پاک‌سازی‌شده (1.5.5.2) متصل به سرور ما
│   ├── jet-elements/                       <-- نسخه old پاک‌سازی‌شده (2.6.13.1) متصل به سرور ما
│   ├── jet-search/                         <-- نسخه old پاک‌سازی‌شده (3.1.1) متصل به سرور ما
│   ├── jet-smart-filters/                  <-- نسخه old پاک‌سازی‌شده (3.3.2) متصل به سرور ما
│   ├── jet-tabs/                           <-- نسخه old پاک‌سازی‌شده (2.3.5) متصل به سرور ما
│   ├── jet-woo-builder/                    <-- نسخه old پاک‌سازی‌شده (2.1.7.3) متصل به سرور ما
│   ├── wp-rocket/                          <-- نسخه old پاک‌سازی‌شده (3.23.5.1) متصل به سرور ما
│   └── old/                                <-- فایل‌های اصلی و دست‌نخورده سرور بیرونی (منبع استخراج لینک دانلود آپدیت‌ها)
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
├── off/                                    <-- افزونه‌های فاقد سیستم یا لینک آپدیتر سرور
│   ├── all-in-one-wp-migration-unlimited-extension-2.86-rewrite/
│   ├── ithemes-security-pro/
│   ├── jet-blocks-1.3.12-rewrite/
│   ├── sitepress-multilingual-cms/
│   ├── wpml-media-translation/
│   └── wpml-string-translation/
│
└── side/                                   <-- قالب‌ها، چایلد تم‌ها و افزونه‌های جانبی (فاقد آپدیتر)
    ├── hello-elementor/                    <-- قالب والد سبک رسمی المنتور
    ├── hello-child/                        <-- چایلد تم اختصاصی المنتور
    ├── jet-elements-dynamic-data-master/   <-- افزونه داده‌های پویا برای JetElements
    ├── jet-engine-custom-visibility-conditions/ <-- افزونه شرایط نمایش سفارشی JetEngine
    └── jet-offcanvas-column-main/          <-- افزونه ستون‌های آف‌کانواس المنتور
```

> ⚠️ **قانون کلیدی `raw/on/` و `raw/on/old/`:**  
> * تمامی پوشه‌ها و فایل‌های داخل `plugins/raw/on/old/` **نسخه اصلی و دست‌نخورده سرور بیرونی** هستند و نباید تغییر داده یا حذف شوند تا همیشه بتوان از لینک دانلود اصلی آن‌ها برای دریافت نسخه‌های جدیدتر استفاده کرد.  
> * در پوشه `plugins/raw/on/` (کنار `old/`)، همان نسخه‌های موجود در `old/` پس از پاک‌سازی و جایگزینی آدرس سرور با **آدرس سرور خودمان** نگهداری می‌شوند تا نسخه قدیمی با سرور اختصاصی ما آماده و قابل تست باشد.

---

## ۲. ساختار کانال‌های انتشار و سرور به‌روزرسانی (`plugins/updates/`)

بسته‌های فشرده نسخه‌های ارتقایافته و پاک‌سازی‌شده همراه با فایل‌های پیکربندی JSON جهت اتصال خودکار وردپرس از طریق **Plugin Update Checker (PUC)** در ۳ پوشه اصلی داخل `plugins/updates/` قرار می‌گیرند و هر پوشه دارای دو زیرشاخه `staging/` (برای تست اولیه) و `production/` (برای انتشار نهایی و دسترسی کاربران پس از تأیید) است:

```text
plugins/updates/
├── up-on/                                  <-- کانال توزیع افزونه‌های دارای لینک و آپدیت سرور (دسته on)
│   ├── staging/                            <-- محیط آزمایشی (Staging) جهت تست آخرین نسخه پیش از انتشار
│   │   ├── jet-elements/
│   │   │   ├── info.json
│   │   │   └── jet-elements-v2.9.2.8.zip
│   │   ├── jet-smart-filters/
│   │   │   ├── info.json
│   │   │   └── jet-smart-filters-v3.8.6.3.zip
│   │   ├── jet-search/
│   │   │   ├── info.json
│   │   │   └── jet-search-v3.6.3.1.zip
│   │   ├── jet-compare-wishlist/
│   │   │   ├── info.json
│   │   │   └── jet-compare-wishlist-v1.5.13.zip
│   │   ├── jet-woo-builder/
│   │   │   ├── info.json
│   │   │   └── jet-woo-builder-v2.3.6.zip
│   │   ├── jet-tabs/
│   │   │   ├── info.json
│   │   │   └── jet-tabs-v2.3.5.zip
│   │   ├── advanced-custom-fields-pro/
│   │   │   ├── info.json
│   │   │   └── advanced-custom-fields-pro-v6.8.11.zip
│   │   ├── wp-rocket/
│   │   │   ├── info.json
│   │   │   └── wp-rocket-v3.23.5.1.zip
│   │   ├── jet-blocks/
│   │   │   ├── info.json
│   │   │   └── jet-blocks-v1.3.12.zip
│   │   └── all-in-one-wp-migration-unlimited-extension/
│   │       ├── info.json
│   │       └── all-in-one-wp-migration-unlimited-extension-v2.86.zip
│   │
│   └── production/                         <-- محیط محصول پایدار (Production) جهت تحویل نهایی به کاربران (تنها پس از تست و تأیید در Staging)
│       ├── jet-blocks/
│       │   ├── info.json
│       │   └── jet-blocks-v1.3.12-[کد_محرمانه_عددی].zip
│       └── all-in-one-wp-migration-unlimited-extension/
│           ├── info.json
│           └── all-in-one-wp-migration-unlimited-extension-v2.86-[کد_محرمانه_عددی].zip
│
├── up-off/                                 <-- کانال توزیع افزونه‌های فاقد آپدیتر سرور اولیه (دسته off)
│   ├── staging/                            <-- محیط آزمایشی جهت تست آخرین نسخه ارتقایافته
│   └── production/                         <-- محیط محصول پایدار جهت فروش و دسترسی کاربران
│
└── up-side/                                <-- کانال توزیع قالب‌ها و افزونه‌های جانبی (دسته side)
    ├── staging/
    └── production/
```

> 🔒 **استاندارد نام‌گذاری بسته‌های پروداکشن (Production Security Naming):**  
> تمامی بسته‌های ارائه‌شده در کانال پروداکشن پس از نام و نسخه، یک خط تیره (`-`) و یک دنباله عددی نامنظم و یکتا (مانند `-4829501832`) دریافت می‌کنند تا آدرس مستقیم فایل فشرده قابل حدس زدن نباشد و تنها از طریق متادیتای مجاز سرور قابل دریافت باشد.

---

## ۳. لیست افزونه‌های فعال و آدرس‌های اندپوینت (Endpoints)

### افزونه WP Rocket (نسخه 3.23.5.1)

* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/wp-rocket/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/wp-rocket/wp-rocket-v3.23.5.1.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **سورس‌های `raw`:**
  * نسخه old با سرور ما: `plugins/raw/on/wp-rocket/` (نسخه `3.23.5.1`)
  * فایل اصلی دست‌نخورده: `plugins/raw/on/old/wp-rocket/`

### افزونه Advanced Custom Fields PRO (نسخه 6.8.11)

* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/advanced-custom-fields-pro/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/advanced-custom-fields-pro/advanced-custom-fields-pro-v6.8.11.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **سورس‌های `raw`:**
  * نسخه old با سرور ما: `plugins/raw/on/advanced-custom-fields-pro/` (نسخه `6.8.11`)
  * فایل اصلی دست‌نخورده: `plugins/raw/on/old/advanced-custom-fields-pro/`

### افزونه JetTabs For Elementor (نسخه 2.3.5)

* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-tabs/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-tabs/jet-tabs-v2.3.5.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **سورس‌های `raw`:**
  * نسخه old با سرور ما: `plugins/raw/on/jet-tabs/` (نسخه `2.3.5`)
  * فایل اصلی دست‌نخورده: `plugins/raw/on/old/jet-tabs/`

### افزونه JetWooBuilder (نسخه 2.3.6)

* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-woo-builder/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-woo-builder/jet-woo-builder-v2.3.6.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **سورس‌های `raw`:**
  * نسخه old با سرور ما: `plugins/raw/on/jet-woo-builder/` (نسخه `2.1.7.3`)
  * فایل اصلی دست‌نخورده: `plugins/raw/on/old/jet-woo-builder/` (نسخه `2.1.7.3`)

### افزونه JetCompareWishlist (نسخه 1.5.13)

* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-compare-wishlist/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-compare-wishlist/jet-compare-wishlist-v1.5.13.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **سورس‌های `raw`:**
  * نسخه old با سرور ما: `plugins/raw/on/jet-compare-wishlist/` (نسخه `1.5.5.2`)
  * فایل اصلی دست‌نخورده: `plugins/raw/on/old/jet-compare-wishlist/` (نسخه `1.5.5.2`)

### افزونه JetSearch (نسخه 3.6.3.1)

* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-search/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-search/jet-search-v3.6.3.1.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **سورس‌های `raw`:**
  * نسخه old با سرور ما: `plugins/raw/on/jet-search/` (نسخه `3.1.1`)
  * فایل اصلی دست‌نخورده: `plugins/raw/on/old/jet-search/` (نسخه `3.1.1`)

### افزونه JetSmartFilters (نسخه 3.8.6.3)

* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-smart-filters/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-smart-filters/jet-smart-filters-v3.8.6.3.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **سورس‌های `raw`:**
  * نسخه old با سرور ما: `plugins/raw/on/jet-smart-filters/` (نسخه `3.3.2`)
  * فایل اصلی دست‌نخورده: `plugins/raw/on/old/jet-smart-filters/` (نسخه `3.3.2`)

### افزونه JetElements For Elementor (نسخه 2.9.2.8)

* **کانال آزمایشی (Staging):**
  * اطلاعات آپدیت: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-elements/info.json`
  * لینک دانلود: `https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/up-on/staging/jet-elements/jet-elements-v2.9.2.8.zip`
* **کانال پایدار (Production):**
  * *طبق قانون مخزن، تا زمان تست و تأیید نهایی در کانال آزمایشی، در پروداکشن قرار نمی‌گیرد.*
* **سورس‌های `raw`:**
  * نسخه old با سرور ما: `plugins/raw/on/jet-elements/` (نسخه `2.6.13.1`)
  * فایل اصلی دست‌نخورده: `plugins/raw/on/old/jet-elements/` (نسخه `2.6.13.1`)

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
