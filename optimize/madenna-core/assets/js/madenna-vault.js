/**
 * Madenna Vault Frontend Controller (نسخه نهایی ۸.۱)
 * ثبت کامل ساختار ابزار در حالت نتیجه با مخفی‌سازی مراحل سوالات (data-vault-hidden-step)
 * Location: /assets/js/madenna-vault.js
 */

(function () {
    'use strict';

    const config = Object.assign({
        ajax_url: '/wp-admin/admin-ajax.php',
        rest_url: '/wp-json/madenna/v1/vault/',
        is_logged_in: false,
        is_tool_page: false,
        user_id: 0,
        user_name: '',
        user_gender: 'male',
        user_birthdate: '',
        tool_id: 0,
        page_id: 0,
        category_slug: '',
        nonce: ''
    }, window.madennaVaultConfig || {});

    function syncVaultConfig() {
        if (window.madennaVaultConfig) {
            Object.assign(config, window.madennaVaultConfig);
        }
        // خواندن پشتیبان از تگ کانتینر ابزار (در صورتی که کش یا اسکریپت کانفیگ به هر دلیلی خالی باشد)
        const wrapperEl = document.querySelector('.madenna-tool-wrapper[data-user-name], .madenna-tool-renderer-wrap[data-user-name]');
        if (wrapperEl) {
            const wrapName = wrapperEl.getAttribute('data-user-name');
            const wrapGender = wrapperEl.getAttribute('data-user-gender');
            const wrapBirthdate = wrapperEl.getAttribute('data-user-birthdate');
            const wrapLoggedIn = wrapperEl.getAttribute('data-is-logged-in') === 'true';
            const wrapUid = parseInt(wrapperEl.getAttribute('data-user-id'), 10) || 0;

            if (wrapLoggedIn) config.is_logged_in = true;
            if (wrapUid > 0 && !config.user_id) config.user_id = wrapUid;
            if (wrapName && (!config.user_name || config.user_name === '')) config.user_name = wrapName;
            if (wrapGender && (!config.user_gender || config.user_gender === '')) config.user_gender = wrapGender;
            if (wrapBirthdate && (!config.user_birthdate || config.user_birthdate === '')) config.user_birthdate = wrapBirthdate;
        }
    }

    let userMobile = '';
    let selectedGender = 'male';
    let currentAuthMode = 'register';
    let currentModalContext = 'header';
    let countdownInterval = null;
    let cachedResultElement = null;
    let userHasInteractedWithTool = false;
    const blobToDataUrlMap = new Map();

    if (window.URL && typeof window.URL.createObjectURL === 'function') {
        const origCreateObjUrl = window.URL.createObjectURL;
        window.URL.createObjectURL = function (obj) {
            const blobUrl = origCreateObjUrl.apply(this, arguments);
            if (obj instanceof Blob && obj.size < 1500000) {
                try {
                    const reader = new FileReader();
                    reader.onloadend = function () {
                        if (reader.result) {
                            blobToDataUrlMap.set(blobUrl, reader.result);
                        }
                    };
                    reader.readAsDataURL(obj);
                } catch (e) {}
            }
            return blobUrl;
        };
    }

    const MALE_SVG = `<svg viewBox="0 0 100 100" width="58" height="58" fill="none" xmlns="http://www.w3.org/2000/svg" class="mad-avatar-svg male">
        <circle cx="50" cy="50" r="48" fill="#E6F4F1" stroke="#99F6E4" stroke-width="2.5"/>
        <path d="M20 88C22 71 33 65 50 65C67 65 78 71 80 88C71 94 61 97 50 97C39 97 29 94 20 88Z" fill="#0F5650"/>
        <path d="M37 65L50 79L63 65" fill="#C2E7E2"/>
        <rect x="42" y="55" width="16" height="16" rx="7" fill="#F5CBA7"/>
        <circle cx="29" cy="47" r="4" fill="#FFD8B8"/>
        <circle cx="71" cy="47" r="4" fill="#FFD8B8"/>
        <ellipse cx="50" cy="46" rx="20" ry="21" fill="#FFD8B8"/>
        <ellipse cx="38" cy="50" rx="3.5" ry="1.8" fill="#F4A28C" opacity="0.45"/>
        <ellipse cx="62" cy="50" rx="3.5" ry="1.8" fill="#F4A28C" opacity="0.45"/>
        <circle cx="41" cy="45" r="2.8" fill="#0F172A"/>
        <circle cx="42" cy="44" r="1" fill="#FFFFFF"/>
        <circle cx="59" cy="45" r="2.8" fill="#0F172A"/>
        <circle cx="60" cy="44" r="1" fill="#FFFFFF"/>
        <path d="M36 39C39 37.5 43 37.8 45 39" stroke="#1E293B" stroke-width="2.4" stroke-linecap="round"/>
        <path d="M55 39C57 37.8 61 37.5 64 39" stroke="#1E293B" stroke-width="2.4" stroke-linecap="round"/>
        <path d="M44 54C46.5 57.5 53.5 57.5 56 54" stroke="#C2410C" stroke-width="2.4" stroke-linecap="round"/>
        <path d="M29 41C28 26 37 17 50 17C64 17 72 25 71 41C67 32 60 28 50 29C40 28 33 32 29 41Z" fill="#1E293B"/>
        <path d="M36 22C42 16 55 15 63 22" stroke="#334155" stroke-width="3" stroke-linecap="round"/>
    </svg>`;

    const FEMALE_SVG = `<svg viewBox="0 0 100 100" width="58" height="58" fill="none" xmlns="http://www.w3.org/2000/svg" class="mad-avatar-svg female">
        <circle cx="50" cy="50" r="48" fill="#FEF3C7" stroke="#FCD34D" stroke-width="2.5"/>
        <path d="M24 50C24 28 35 16 50 16C65 16 76 28 76 50C76 64 72 78 68 84H32C28 78 24 64 24 50Z" fill="#3E2723"/>
        <path d="M22 88C24 72 34 66 50 66C66 66 76 72 78 88C69 94 60 97 50 97C40 97 31 94 22 88Z" fill="#D97706"/>
        <path d="M38 66L50 78L62 66" fill="#FDE68A"/>
        <rect x="43" y="56" width="14" height="15" rx="6" fill="#F5CBA7"/>
        <ellipse cx="50" cy="46" rx="19" ry="21" fill="#FFD8B8"/>
        <ellipse cx="38" cy="50" rx="3.5" ry="2" fill="#F4A28C" opacity="0.6"/>
        <ellipse cx="62" cy="50" rx="3.5" ry="2" fill="#F4A28C" opacity="0.6"/>
        <circle cx="41" cy="45" r="2.8" fill="#1E293B"/>
        <circle cx="42" cy="44" r="1" fill="#FFFFFF"/>
        <circle cx="59" cy="45" r="2.8" fill="#1E293B"/>
        <circle cx="60" cy="44" r="1" fill="#FFFFFF"/>
        <path d="M37 39C39 37.5 43 37.5 45 39" stroke="#3E2723" stroke-width="2" stroke-linecap="round"/>
        <path d="M55 39C57 37.5 61 37.5 63 39" stroke="#3E2723" stroke-width="2" stroke-linecap="round"/>
        <path d="M44 54C46.5 57.5 53.5 57.5 56 54" stroke="#E11D48" stroke-width="2.4" stroke-linecap="round"/>
        <path d="M30 42C31 28 40 22 50 22C60 22 69 28 70 42C65 34 58 31 50 34C42 31 35 34 30 42Z" fill="#4E342E"/>
        <circle cx="30" cy="49" r="2.5" fill="#F59E0B"/>
        <circle cx="70" cy="49" r="2.5" fill="#F59E0B"/>
    </svg>`;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMadennaVault);
    } else {
        initMadennaVault();
    }

    function getLiveToolContainer() {
        if (document.querySelector('.mad-vault-dashboard-wrapper')) {
            return null;
        }
        const wrap = document.querySelector('.madenna-tool-wrapper:not(#mad-vault-result-modal-body), .madenna-tool-renderer-wrap');
        if (!wrap) return null;
        if (wrap.closest('.mad-vault-dashboard-wrapper') || wrap.closest('#mad-vault-result-preview-modal')) {
            return null;
        }
        return wrap;
    }

    function initMadennaVault() {
        syncVaultConfig();
        createModalMarkup();
        bindEvents();

        // اجرای آنی پر کردن فیلدها
        autoPopulateToolUserFields();

        if (getLiveToolContainer()) {
            setupUniversalToolResultWatcher();
        }

        // تکرار پر کردن برای ابزارهای داینامیک یا ساخته‌شده با جمینی که با تاخیر لود می‌شوند
        [50, 150, 300, 600, 1000, 1800, 2800, 4500].forEach(delay => {
            setTimeout(() => {
                syncVaultConfig();
                autoPopulateToolUserFields();
            }, delay);
        });

        // رصد تغییرات DOM برای ابزارهای چند مرحله‌ای (Multi-step)
        if (window.MutationObserver && !window._madennaToolObserverInit) {
            window._madennaToolObserverInit = true;
            let observerTimeout = null;
            const observer = new MutationObserver(() => {
                if (observerTimeout) clearTimeout(observerTimeout);
                observerTimeout = setTimeout(() => {
                    syncVaultConfig();
                    autoPopulateToolUserFields();
                }, 50);
            });
            const obsTarget = getLiveToolContainer() || document.body;
            if (obsTarget) {
                observer.observe(obsTarget, { childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class'] });
            }
        }

    }

    function autoPopulateToolUserFields(customData) {
        syncVaultConfig();

        let activeName = config.user_name || '';
        let activeGender = config.user_gender || '';
        let activeBirthdate = config.user_birthdate || '';

        if (typeof customData === 'string') {
            activeName = customData;
        } else if (customData && typeof customData === 'object') {
            if (customData.name) activeName = customData.name;
            if (customData.gender) activeGender = customData.gender;
            if (customData.birthdate) activeBirthdate = customData.birthdate;
        }

        const isFemale = (activeGender === 'female' || activeGender === 'woman' || activeGender === 'girl' || activeGender === 'زن' || activeGender === 'خانم' || activeGender === 'دختر');
        const isMale   = (activeGender === 'male' || activeGender === 'man' || activeGender === 'boy' || activeGender === 'مرد' || activeGender === 'آقا' || activeGender === 'پسر');
        const genderFa = isFemale ? 'خانم' : 'آقا';
        const genderFaAlt = isFemale ? 'زن' : 'مرد';

        // استخراج و نرمال‌سازی اجزای تاریخ تولد
        let birthYear = '', birthMonth = '', birthDay = '', calculatedAge = '';
        if (activeBirthdate) {
            const cleanBdate = String(activeBirthdate).replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
            const parts = cleanBdate.split(/[\/\-\.\s]/).map(p => p.trim()).filter(Boolean);
            if (parts.length >= 1) {
                if (parts[0].length === 4) {
                    birthYear = parts[0];
                    birthMonth = parts[1] || '';
                    birthDay = parts[2] || '';
                } else if (parts[2] && parts[2].length === 4) {
                    birthYear = parts[2];
                    birthMonth = parts[1] || '';
                    birthDay = parts[0] || '';
                }

                if (birthYear) {
                    const yNum = parseInt(birthYear, 10);
                    if (yNum > 1300 && yNum < 1420) {
                        calculatedAge = String(Math.max(1, 1403 - yNum));
                    } else if (yNum > 1920 && yNum < 2030) {
                        calculatedAge = String(Math.max(1, new Date().getFullYear() - yNum));
                    }
                }
            }
        }

        const bYearNum = birthYear ? parseInt(birthYear, 10) : 0;
        const bMonthNum = birthMonth ? parseInt(birthMonth, 10) : 0;
        const bDayNum = birthDay ? parseInt(birthDay, 10) : 0;

        // اسامی ماه‌های شمسی برای مچ کردن هوشمند با منوهای متنی
        const shamsiMonthNames = {
            1: ['فروردین', 'farvardin', '1', '01'],
            2: ['اردیبهشت', 'ordibehesht', '2', '02'],
            3: ['خرداد', 'khordad', '3', '03'],
            4: ['تیر', 'tir', '4', '04'],
            5: ['مرداد', 'mordad', '5', '05'],
            6: ['شهریور', 'shahrivar', '6', '06'],
            7: ['مهر', 'mehr', '7', '07'],
            8: ['آبان', 'aban', '8', '08'],
            9: ['آذر', 'azar', '9', '09'],
            10: ['دی', 'dey', '10'],
            11: ['بهمن', 'bahman', '11'],
            12: ['اسفند', 'esfand', '12']
        };

        // پل ارتباطی سراسری MadennaUser
        window.MadennaUser = {
            isLoggedIn: !!config.is_logged_in,
            id: parseInt(config.user_id, 10) || 0,
            name: activeName,
            gender: activeGender,
            genderLabel: genderFa,
            genderText: genderFaAlt,
            birthdate: activeBirthdate,
            birthYear: birthYear,
            birthMonth: birthMonth,
            birthDay: birthDay,
            age: calculatedAge ? parseInt(calculatedAge, 10) : '',
            mobile: userMobile || ''
        };

        try {
            window.dispatchEvent(new CustomEvent('madenna_user_ready', { detail: window.MadennaUser }));
        } catch (e) {}

        // جستجو در کانتینر ابزار یا تگ بدنه
        const toolWrap = getLiveToolContainer() || document.querySelector('.madenna-tool-wrapper, .madenna-tool-renderer-wrap, [id^="madenna-tool-"]') || (document.body && typeof document.body.querySelectorAll === 'function' ? document.body : document);
        if (!toolWrap) return;

        function setFieldValue(input, val) {
            if (!input || val === undefined || val === null || val === '') return;
            if (!input.value || input.value.trim() === '') {
                input.value = val;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        // ==========================================
        // ۱. پر کردن خودکار فیلدهای نام کاربر
        // ==========================================
        if (activeName) {
            // الف) سلکتورهای مستقیم و تضمین‌شده
            const directNameInputs = toolWrap.querySelectorAll(
                'input[name*="name" i], input[id*="name" i], input[placeholder*="نام"], input[placeholder*="آرش"], input[placeholder*="سارا"], input[placeholder*="علی"], input[data-user-name-input], input[data-user-name], input[name="fullname"], input[name="user_name"], input[id="fullname"], input[id="user_name"]'
            );
            directNameInputs.forEach(inp => {
                const n = (inp.name || '').toLowerCase();
                const id = (inp.id || '').toLowerCase();
                if (n.includes('username') || n.includes('file') || id.includes('username') || id.includes('file') || inp.type === 'file' || inp.type === 'hidden') return;
                setFieldValue(inp, activeName);
            });

            // ب) جستجوی هوشمند در تمام اینپوت‌های متنی با برچسب یا متن والد
            const allTextInputs = toolWrap.querySelectorAll('input:not([type="hidden"]):not([type="radio"]):not([type="checkbox"]):not([type="button"]):not([type="submit"]):not([type="file"]):not([type="number"]):not([type="tel"]):not([type="password"]):not([type="email"])');
            allTextInputs.forEach(inp => {
                if (inp.value && inp.value.trim() !== '') return;
                const nAttr = (inp.name || '').toLowerCase();
                const iAttr = (inp.id || '').toLowerCase();
                const pAttr = (inp.placeholder || '').toLowerCase();
                if (nAttr.includes('phone') || nAttr.includes('mobile') || nAttr.includes('birth') || nAttr.includes('year') || nAttr.includes('age') || nAttr.includes('code')) return;
                if (iAttr.includes('phone') || iAttr.includes('mobile') || iAttr.includes('birth') || iAttr.includes('year') || iAttr.includes('age') || iAttr.includes('code')) return;

                // بررسی متن نزدیک به اینپوت یا برچسب بالای آن (مانند تصویر ۱: "نام و نام خانوادگی (یا نام شما) *")
                let nearText = '';
                const lbl = inp.closest('label') || (inp.id ? toolWrap.querySelector(`label[for="${inp.id}"]`) : null);
                if (lbl) nearText += ' ' + lbl.textContent;
                const parentBox = inp.closest('.name-section, .input-group, .form-group, div') || inp.parentElement;
                if (parentBox) nearText += ' ' + parentBox.textContent.slice(0, 150);

                nearText = nearText.toLowerCase();
                if (nearText.includes('نام و نام خانوادگی') || nearText.includes('نام شما') || nearText.includes('نام یا نام خانوادگی') || pAttr.includes('آرش علوی') || pAttr.includes('سارا محمدی')) {
                    if (!nearText.includes('شماره') && !nearText.includes('موبایل') && !nearText.includes('تلفن')) {
                        setFieldValue(inp, activeName);
                    }
                }
            });

            // ج) تگ‌های متنی نمایش‌دهنده نام
            const namePlaceholders = toolWrap.querySelectorAll('.madenna-user-name, [data-madenna-user-name], #user-name-display, .user-name-display');
            namePlaceholders.forEach(el => {
                el.textContent = activeName;
            });
        }
        // ==========================================
        // ۲. پر کردن خودکار جنسیت (دکمه‌ها، کارت‌ها، رادیوها، سلکت‌ها)
        // ==========================================
        if (activeGender && (isFemale || isMale)) {
            // الف) دکمه‌ها و المان‌های کلیک‌پذیر (مانند تصویر ۱ و ۲: دکمه‌های آقا/خانم و کاراکتر پسر/کاراکتر دختر)
            const allClickables = toolWrap.querySelectorAll('button, [role="button"], .gender-card, .gender-option, .gender-btn, [data-gender], [data-sex], [data-value], div, label');
            allClickables.forEach(el => {
                // اگر المان حاوی فرزندان زیاد است، فقط المان‌های نهایی یا المان‌های مشخص‌شده را هدف قرار می‌دهیم
                if (el.tagName === 'DIV' && el.children.length > 5 && !el.classList.contains('gender-card') && !el.classList.contains('gender-option')) return;
                
                const valAttr  = (el.getAttribute('data-gender') || el.getAttribute('data-sex') || el.getAttribute('data-value') || '').toLowerCase().trim();
                const clsAttr  = (typeof el.className === 'string' ? el.className : '').toLowerCase();
                const idAttr   = (el.id || '').toLowerCase();
                const txt      = (el.innerText || el.textContent || '').trim().toLowerCase();

                let isTargetFemale = false;
                let isTargetMale   = false;

                // تطبیق خانم / دختر
                if (valAttr === 'female' || valAttr === 'woman' || valAttr === 'girl' || valAttr === 'f' || valAttr === 'زن' || valAttr === 'خانم' || valAttr === 'دختر') {
                    isTargetFemale = true;
                } else if (txt === 'خانم' || txt === 'زن' || txt.includes('کاراکتر دختر') || txt.includes('دختر') || clsAttr.includes('female')) {
                    // اطمینان از اینکه همزمان حاوی کلمه آقا/پسر نیست
                    if (!txt.includes('آقا') && !txt.includes('پسر') && !txt.includes('مرد')) {
                        isTargetFemale = true;
                    }
                }

                // تطبیق آقا / پسر
                if (valAttr === 'male' || valAttr === 'man' || valAttr === 'boy' || valAttr === 'm' || valAttr === 'مرد' || valAttr === 'آقا' || valAttr === 'پسر') {
                    isTargetMale = true;
                } else if (txt === 'آقا' || txt === 'مرد' || txt.includes('کاراکتر پسر') || txt.includes('پسر') || clsAttr.includes('male')) {
                    if (!txt.includes('خانم') && !txt.includes('دختر') && !txt.includes('زن')) {
                        isTargetMale = true;
                    }
                }

                let shouldSelect = (isFemale && isTargetFemale) || (isMale && isTargetMale);
                if (shouldSelect) {
                    // بررسی وضعیت فعال بودن فعلی
                    const isAlreadyActive = (el.classList && (el.classList.contains('active') || el.classList.contains('selected'))) || el.getAttribute('aria-pressed') === 'true' || (typeof el.className === 'string' && (el.className.includes('active') || el.className.includes('selected')));
                    if (!isAlreadyActive) {
                        try {
                            el.click();
                            el.dispatchEvent(new Event('click', { bubbles: true }));
                        } catch (e) {}
                    }
                    // اگر داخل این المان دکمه رادیو یا چک‌باکس وجود دارد
                    const innerRadio = el.querySelector('input[type="radio"], input[type="checkbox"]');
                    if (innerRadio && !innerRadio.checked) {
                        innerRadio.checked = true;
                        innerRadio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });

            // ب) رادیوباتن‌ها
            const genderRadios = toolWrap.querySelectorAll('input[type="radio"]');
            genderRadios.forEach(radio => {
                const val   = (radio.value || '').toLowerCase().trim();
                const name  = (radio.name || '').toLowerCase();
                const id    = (radio.id || '').toLowerCase();
                const label = radio.closest('label') || (radio.id ? toolWrap.querySelector(`label[for="${radio.id}"]`) : null);
                const txt   = label ? label.textContent.toLowerCase() : '';

                let isTargetFemale = false;
                let isTargetMale   = false;

                if (['female', 'woman', 'f', 'زن', 'خانم', 'دختر', '2'].includes(val) || txt.includes('خانم') || txt.includes('دختر') || txt.includes('زن')) {
                    isTargetFemale = true;
                }
                if (['male', 'man', 'm', 'مرد', 'آقا', 'پسر', '1'].includes(val) || txt.includes('آقا') || txt.includes('پسر') || txt.includes('مرد')) {
                    isTargetMale = true;
                }

                if ((isFemale && isTargetFemale) || (isMale && isTargetMale)) {
                    if (!radio.checked) {
                        radio.checked = true;
                        radio.dispatchEvent(new Event('input', { bubbles: true }));
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });

            // ج) منوهای کشویی Select
            const genderSelects = toolWrap.querySelectorAll('select');
            genderSelects.forEach(sel => {
                const name = (sel.name || '').toLowerCase();
                const id   = (sel.id || '').toLowerCase();
                let isGenderSelect = (name.includes('gender') || name.includes('sex') || id.includes('gender') || id.includes('sex'));
                
                // بررسی گزینه‌های داخل سلکت برای تشخیص فیلد جنسیت
                let foundMatch = false;
                Array.from(sel.options).forEach(opt => {
                    const val = (opt.value || '').toLowerCase().trim();
                    const txt = (opt.textContent || '').trim().toLowerCase();
                    if (txt.includes('خانم') || txt.includes('آقا') || txt.includes('مرد') || txt.includes('زن') || val === 'male' || val === 'female') {
                        isGenderSelect = true;
                    }
                    if (isFemale) {
                        if (['female', 'woman', 'f', 'زن', 'خانم', 'دختر', '2'].includes(val) || txt.includes('خانم') || txt.includes('زن') || txt.includes('دختر')) {
                            sel.value = opt.value;
                            foundMatch = true;
                        }
                    } else if (isMale) {
                        if (['male', 'man', 'm', 'مرد', 'آقا', 'پسر', '1'].includes(val) || txt.includes('آقا') || txt.includes('مرد') || txt.includes('پسر')) {
                            sel.value = opt.value;
                            foundMatch = true;
                        }
                    }
                });

                if (isGenderSelect && foundMatch) {
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                    sel.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });

            // تگ‌های متنی
            const genderPlaceholders = toolWrap.querySelectorAll('.madenna-user-gender, [data-madenna-user-gender]');
            genderPlaceholders.forEach(el => { el.textContent = genderFa; });
        }

        // ==========================================
        // ۳. پر کردن خودکار تاریخ تولد (روز، ماه، سال) و سن
        // ==========================================
        if (activeBirthdate) {
            // الف) منوهای کشویی Select (مانند تصویر ۱ و ۲: روز تولد، ماه تولد، سال تولد)
            const allSelects = toolWrap.querySelectorAll('select');
            allSelects.forEach(sel => {
                const nameAttr = (sel.name || '').toLowerCase();
                const idAttr   = (sel.id || '').toLowerCase();
                const firstOpt = sel.options.length > 0 ? (sel.options[0].textContent || '').toLowerCase() : '';
                const allOptTexts = Array.from(sel.options).slice(0, 5).map(o => (o.textContent || '').trim()).join(' ');

                // ۱. تشخیص منوی روز تولد (Day)
                const isDaySelect = (nameAttr.includes('day') || idAttr.includes('day') || firstOpt.includes('روز') || firstOpt.includes('روز تولد') || nameAttr.includes('bday') || idAttr.includes('bday'));
                if (isDaySelect && bDayNum > 0) {
                    let matched = false;
                    Array.from(sel.options).forEach(opt => {
                        const valNum = parseInt(opt.value, 10);
                        const txtNum = parseInt(opt.textContent, 10);
                        if (valNum === bDayNum || txtNum === bDayNum) {
                            sel.value = opt.value;
                            matched = true;
                        }
                    });
                    if (matched) {
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        sel.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    return;
                }

                // ۲. تشخیص منوی ماه تولد (Month)
                const isMonthSelect = (nameAttr.includes('month') || idAttr.includes('month') || firstOpt.includes('ماه') || firstOpt.includes('ماه تولد') || nameAttr.includes('bmonth') || idAttr.includes('bmonth'));
                if (isMonthSelect && bMonthNum > 0) {
                    const validMonthTokens = shamsiMonthNames[bMonthNum] || [String(bMonthNum)];
                    let matched = false;
                    Array.from(sel.options).forEach(opt => {
                        const v = (opt.value || '').trim().toLowerCase();
                        const t = (opt.textContent || '').trim().toLowerCase();
                        const vNum = parseInt(v, 10);
                        if (vNum === bMonthNum) {
                            sel.value = opt.value;
                            matched = true;
                        } else {
                            for (let token of validMonthTokens) {
                                if (v === token || t.includes(token)) {
                                    sel.value = opt.value;
                                    matched = true;
                                    break;
                                }
                            }
                        }
                    });
                    if (matched) {
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        sel.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    return;
                }

                // ۳. تشخیص منوی سال تولد (Year)
                const isYearSelect = (nameAttr.includes('year') || idAttr.includes('year') || firstOpt.includes('سال') || firstOpt.includes('سال تولد') || nameAttr.includes('byear') || idAttr.includes('byear'));
                if (isYearSelect && bYearNum > 0) {
                    let matched = false;
                    Array.from(sel.options).forEach(opt => {
                        const vNum = parseInt(opt.value, 10);
                        const tNum = parseInt(opt.textContent, 10);
                        if (vNum === bYearNum || tNum === bYearNum) {
                            sel.value = opt.value;
                            matched = true;
                        }
                    });
                    if (matched) {
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        sel.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    return;
                }
            });

            // ب) فیلدهای Input برای تاریخ تولد، سال، ماه، روز و سن
            const allInputs = toolWrap.querySelectorAll('input:not([type="hidden"]):not([type="radio"]):not([type="checkbox"])');
            allInputs.forEach(inp => {
                const nameAttr = (inp.name || '').toLowerCase();
                const idAttr   = (inp.id || '').toLowerCase();
                const phAttr   = (inp.placeholder || '').toLowerCase();

                // فیلد کامل تاریخ تولد
                if (nameAttr.includes('birth') || idAttr.includes('birth') || nameAttr.includes('bdate') || idAttr.includes('bdate') || phAttr.includes('تاریخ تولد')) {
                    if (!nameAttr.includes('year') && !nameAttr.includes('month') && !nameAttr.includes('day') && !idAttr.includes('year') && !idAttr.includes('month') && !idAttr.includes('day') && !phAttr.includes('سال') && !phAttr.includes('ماه') && !phAttr.includes('روز')) {
                        setFieldValue(inp, activeBirthdate);
                    }
                }

                // فیلد سال تولد
                if ((nameAttr.includes('year') || idAttr.includes('year') || phAttr.includes('سال تولد') || phAttr.includes('سال')) && bYearNum > 0) {
                    setFieldValue(inp, birthYear);
                }

                // فیلد ماه تولد
                if ((nameAttr.includes('month') || idAttr.includes('month') || phAttr.includes('ماه تولد') || phAttr.includes('ماه')) && bMonthNum > 0) {
                    setFieldValue(inp, birthMonth);
                }

                // فیلد روز تولد
                if ((nameAttr.includes('day') || idAttr.includes('day') || phAttr.includes('روز تولد') || phAttr.includes('روز')) && bDayNum > 0) {
                    setFieldValue(inp, birthDay);
                }

                // فیلد سن
                if ((nameAttr.includes('age') || idAttr.includes('age') || phAttr.includes('سن شما') || phAttr.includes('سن')) && calculatedAge) {
                    if (!nameAttr.includes('message') && !idAttr.includes('message') && !nameAttr.includes('page')) {
                        setFieldValue(inp, calculatedAge);
                    }
                }
            });

            const bdatePlaceholders = toolWrap.querySelectorAll('.madenna-user-birthdate, [data-madenna-user-birthdate]');
            bdatePlaceholders.forEach(el => { el.textContent = activeBirthdate; });
            const agePlaceholders = toolWrap.querySelectorAll('.madenna-user-age, [data-madenna-user-age]');
            agePlaceholders.forEach(el => { el.textContent = calculatedAge; });
        }
    }

    function createModalMarkup() {
        if (document.getElementById('madenna-vault-modal')) return;

        const modalHtml = `
        <div id="madenna-vault-modal" class="mad-vault-overlay" style="display:none; position:fixed; top:0; right:0; bottom:0; left:0; width:100vw; height:100vh; background:rgba(15,23,42,0.72); z-index:9999999; align-items:center; justify-content:center; padding:16px; direction:rtl;">
            <div class="mad-vault-window" style="background:#fff; width:100%; max-width:450px; border-radius:22px; padding:26px 24px; position:relative; margin:auto;">
                <button type="button" class="mad-vault-close" id="mad-vault-close-btn" aria-label="بستن">&times;</button>
                
                <div class="mad-vault-header">
                    <div id="mad-vault-context-badge" class="mad-vault-badge" style="display:none;">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>پرونده ابزارها و محاسبات مادنا</span>
                    </div>
                    <h3 id="mad-vault-modal-title" class="mad-vault-title">ورود یا عضویت در مادنا</h3>
                    <p id="mad-vault-modal-desc" class="mad-vault-desc">برای دسترسی به پرونده، سوابق و پیشنهادات اختصاصی وارد شوید.</p>
                </div>

                <div class="mad-vault-auth-tabs" id="mad-vault-auth-tabs-bar">
                    <button type="button" class="mad-vault-auth-tab active" data-tab="register">ثبت‌نام و ایجاد پرونده</button>
                    <button type="button" class="mad-vault-auth-tab" data-tab="login">ورود به حساب</button>
                </div>

                <div id="mad-vault-step-phone" class="mad-vault-step">
                    <div id="mad-vault-register-fields">
                        <div class="mad-vault-gender-selector">
                            <label>تصویر کاراکتر پروفایل خود را انتخاب کنید:</label>
                            <div class="mad-vault-gender-options">
                                <div class="mad-vault-gender-card male selected" data-gender="male">
                                    <div class="avatar-render">${MALE_SVG}</div>
                                    <span class="gender-title">آقا</span>
                                </div>
                                <div class="mad-vault-gender-card female" data-gender="female">
                                    <div class="avatar-render">${FEMALE_SVG}</div>
                                    <span class="gender-title">خانم</span>
                                </div>
                            </div>
                        </div>

                        <div class="mad-vault-input-group">
                            <label for="mad-vault-name-input">نام و نام خانوادگی:</label>
                            <div class="mad-vault-input-wrapper">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" id="mad-vault-name-input" placeholder="مثال: علی حسینی" autocomplete="name">
                            </div>
                            <span id="mad-vault-name-error" class="mad-vault-error-msg"></span>
                        </div>
                    </div>

                    <div class="mad-vault-input-group">
                        <label for="mad-vault-mobile-input">شماره موبایل شما:</label>
                        <div class="mad-vault-input-wrapper">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                            <input type="tel" id="mad-vault-mobile-input" placeholder="۰۹۱۲۳۴۵۶۷۸۹" maxlength="11" dir="ltr" autocomplete="tel">
                        </div>
                        <span id="mad-vault-phone-error" class="mad-vault-error-msg"></span>
                    </div>

                    <button type="button" id="mad-vault-send-otp-btn" class="mad-vault-btn-primary">
                        <span id="mad-vault-send-btn-text">دریافت کد تایید پیامکی</span>
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>

                    <div class="mad-vault-notice">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>پیامک کد تایید از طریق زیرساخت مرکزی ما (<strong>سئوریتم</strong>) ارسال خواهد شد.</span>
                    </div>
                </div>

                <div id="mad-vault-step-code" class="mad-vault-step" style="display: none;">
                    <div class="mad-vault-input-group">
                        <div class="mad-vault-label-row" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <label for="mad-vault-code-input">کد ۴ رقمی ارسال‌شده:</label>
                            <span id="mad-vault-edit-phone-btn" style="font-size:11px; color:#0f5650; cursor:pointer; font-weight:700;">ویرایش شماره</span>
                        </div>
                        <div class="mad-vault-input-wrapper">
                            <i class="fa-solid fa-key"></i>
                            <input type="text" id="mad-vault-code-input" placeholder="- - - -" maxlength="4" dir="ltr" inputmode="numeric">
                        </div>
                        <span id="mad-vault-code-error" class="mad-vault-error-msg"></span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; font-size:12px;">
                        <span id="mad-vault-timer-text">زمان باقی‌مانده: <b id="mad-vault-countdown">02:00</b></span>
                        <button type="button" id="mad-vault-resend-btn" style="display:none; background:none; border:none; color:#0f5650; font-weight:800; cursor:pointer;">ارسال مجدد کد</button>
                    </div>

                    <button type="button" id="mad-vault-verify-btn" class="mad-vault-btn-primary">
                        <span id="mad-vault-verify-btn-text">تایید و ورود</span>
                        <i class="fa-solid fa-check"></i>
                    </button>
                </div>
            </div>
        </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    function bindEvents() {
        const modal      = document.getElementById('madenna-vault-modal');
        const closeBtn   = document.getElementById('mad-vault-close-btn');
        const sendOtpBtn = document.getElementById('mad-vault-send-otp-btn');
        const verifyBtn  = document.getElementById('mad-vault-verify-btn');
        const resendBtn  = document.getElementById('mad-vault-resend-btn');
        const editPhone  = document.getElementById('mad-vault-edit-phone-btn');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                if (currentModalContext === 'tool' && !config.is_logged_in) return;
                closeModal();
            });
        }
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (currentModalContext === 'tool' && !config.is_logged_in) return;
                if (e.target === modal) closeModal();
            });
        }

        document.querySelectorAll('.mad-vault-auth-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.mad-vault-auth-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                setAuthMode(this.getAttribute('data-tab'));
            });
        });

        document.querySelectorAll('.mad-vault-gender-card').forEach(card => {
            card.addEventListener('click', function () {
                document.querySelectorAll('.mad-vault-gender-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                selectedGender = this.getAttribute('data-gender');
            });
        });

        if (sendOtpBtn) sendOtpBtn.addEventListener('click', handleSendOtp);
        if (verifyBtn) verifyBtn.addEventListener('click', handleVerifyOtp);
        if (resendBtn) resendBtn.addEventListener('click', handleSendOtp);

        if (editPhone) editPhone.addEventListener('click', () => {
            clearInterval(countdownInterval);
            switchStep('phone');
        });

        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('#mad-vault-header-login-btn, .mad-vault-header-login-btn, .mad-vault-open-modal-trigger, [href="#madenna-login"]');
            if (trigger) {
                e.preventDefault();
                e.stopPropagation();
                openModal('header');
                return;
            }

            const liveToolWrap = getLiveToolContainer();
            if (liveToolWrap && liveToolWrap.contains(e.target)) {
                userHasInteractedWithTool = true;
                setTimeout(scanForRevealedResultInTool, 80);
                setTimeout(scanForRevealedResultInTool, 350);
                setTimeout(scanForRevealedResultInTool, 750);
            }
        }, true);

        document.addEventListener('madenna_tool_result', function (e) {
            const liveToolWrap = getLiveToolContainer();
            if (!liveToolWrap) return;

            userHasInteractedWithTool = true;
            const detail = e.detail || {};
            const targetEl = detail.element || liveToolWrap.querySelector('#madenna-tool-result, .madenna-tool-result');
            if (targetEl && liveToolWrap.contains(targetEl)) {
                handleResultRevealed(resolveCompleteResultElement(targetEl, liveToolWrap));
            }
        });
    }

    function setAuthMode(mode) {
        currentAuthMode = mode;
        const regFields     = document.getElementById('mad-vault-register-fields');
        const sendBtnText   = document.getElementById('mad-vault-send-btn-text');
        const verifyBtnText = document.getElementById('mad-vault-verify-btn-text');
        clearErrors();

        if (mode === 'login') {
            regFields.style.display = 'none';
            sendBtnText.textContent = 'ارسال کد ورود';
            verifyBtnText.textContent = 'تایید و ورود به حساب';
        } else {
            regFields.style.display = 'block';
            sendBtnText.textContent = currentModalContext === 'tool' ? 'دریافت کد و مشاهده نتیجه' : 'دریافت کد و ایجاد پرونده';
            verifyBtnText.textContent = currentModalContext === 'tool' ? 'تایید و نمایش فوری نتیجه' : 'تایید و تکمیل ثبت‌نام';
        }
    }

    function setupUniversalToolResultWatcher() {
        const toolWrap = getLiveToolContainer();
        if (!toolWrap) return;

        toolWrap.querySelectorAll('div, section, article').forEach(el => {
            const st = window.getComputedStyle(el);
            const isHidden = st.display === 'none' || st.visibility === 'hidden' || el.classList.contains('hidden');
            if (isHidden) {
                el.setAttribute('data-was-hidden-initially', 'true');
            } else if (el.innerText.trim().length > 20) {
                el.setAttribute('data-initial-visible', 'true');
            }
        });

        const observer = new MutationObserver(() => {
            if (!userHasInteractedWithTool) return;
            scanForRevealedResultInTool();
        });

        observer.observe(toolWrap, {
            childList: true,
            subtree: true,
            characterData: true,
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }

    function isElementVisible(el) {
        if (!el) return false;
        const st = window.getComputedStyle(el);
        return st.display !== 'none' && st.visibility !== 'hidden' && st.opacity !== '0' && !el.classList.contains('hidden');
    }

    function isNonResultScreenOrForm(node) {
        if (!node || node.nodeType !== 1) return false;
        const id  = (node.id || '').toLowerCase();
        const cls = (typeof node.className === 'string' ? node.className : '').toLowerCase();

        if (node.tagName === 'FORM') return true;
        if (node.matches && node.matches('#madenna-tool-inputs, [data-tool-inputs]')) return true;
        if (node.querySelector && node.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea')) return true;

        const nonResultKeywords = ['quiz', 'question', 'intro', 'welcome', 'start-screen', 'start-view', 'start-sec', 'input-sec', 'inputs-sec', 'form-sec', 'step-1', 'step1'];
        for (let i = 0; i < nonResultKeywords.length; i++) {
            const kw = nonResultKeywords[i];
            if (id.includes(kw) || cls.includes(kw)) {
                if (!id.includes('result') && !cls.includes('result')) {
                    return true;
                }
            }
        }
        return false;
    }

    function resolveCompleteResultElement(el, toolWrap) {
        if (!el || !toolWrap) return el;

        function hasInteractiveInputs(node) {
            if (!node) return false;
            return !!node.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea');
        }

        function hasQuestionOrIntroScreens(node) {
            if (!node) return false;
            return !!node.querySelector(
                '#madenna-tool-inputs, [data-tool-inputs], #quiz-screen, #question-screen, #quiz-section, #questions-section, #start-screen, #welcome-screen, #intro-screen, #input-section, #inputs-section, #form-section'
            );
        }

        const standardRes = toolWrap.querySelector('#madenna-tool-result, [data-tool-result="true"], .madenna-tool-result');
        if (standardRes && isElementVisible(standardRes) && standardRes.innerText.trim().length > 20 && !hasQuestionOrIntroScreens(standardRes)) {
            return standardRes;
        }

        // جستجو از بیرونی‌ترین کانتینر نتیجه به داخل تا کل کارنامه (نه فقط یک کارت داخلی) انتخاب شود
        const bestOuterResult = el.closest(
            '#madenna-tool-result, [data-tool-result="true"], .madenna-tool-result, #result-screen, #results-screen, #result-section, #results-section, #result-view, #results-view, #result-container, #results-container, #final-result, #quiz-result, #result, #results'
        );
        if (bestOuterResult && toolWrap.contains(bestOuterResult) && bestOuterResult !== toolWrap && !hasInteractiveInputs(bestOuterResult) && !hasQuestionOrIntroScreens(bestOuterResult)) {
            el = bestOuterResult;
        }

        let best = el;
        let curr = el.parentElement;
        while (curr && curr !== toolWrap && !curr.classList.contains('madenna-tool-wrapper') && !curr.classList.contains('madenna-tool-renderer-wrap')) {
            if (!isElementVisible(curr)) break;
            if (hasInteractiveInputs(curr) || hasQuestionOrIntroScreens(curr)) break;

            const sibs = Array.from(curr.children).filter(c => !['STYLE', 'LINK', 'SCRIPT'].includes(c.tagName));
            const hasInputOrQuestionSibling = sibs.some(s => {
                if (s === best) return false;
                const sid  = (s.id || '').toLowerCase();
                const scls = (typeof s.className === 'string' ? s.className : '').toLowerCase();
                if (sid.includes('modal') || scls.includes('modal') || s.getAttribute('role') === 'dialog') return false;
                if (isNonResultScreenOrForm(s)) return true;
                if (s.getAttribute('data-initial-visible') === 'true' && best.getAttribute('data-was-hidden-initially') === 'true') return true;
                return false;
            });

            if (hasInputOrQuestionSibling) break;
            best = curr;
            curr = curr.parentElement;
        }
        return best;
    }

    function scanForRevealedResultInTool() {
        if (!userHasInteractedWithTool) return;
        const toolWrap = getLiveToolContainer();
        if (!toolWrap) return;

        const directResult = toolWrap.querySelector('#madenna-tool-result, .madenna-tool-result, [data-tool-result="true"], [data-tool-result]');
        if (directResult && directResult.getAttribute('data-vault-processed') !== 'true') {
            if (isElementVisible(directResult) && directResult.innerText.trim().length > 20) {
                handleResultRevealed(resolveCompleteResultElement(directResult, toolWrap));
                return;
            }
        }

        const selectors = [
            '#result-screen',
            '#results-screen',
            '#result-section',
            '#results-section',
            '#result-view',
            '#results-view',
            '#result-container',
            '#results-container',
            '#final-result',
            '#quiz-result',
            '#result',
            '#results',
            '#result-box',
            '#result-card',
            '[id*="result-screen" i]',
            '[id*="result-section" i]',
            '[class*="result-screen" i]',
            '[class*="result-section" i]',
            '[id*="result" i]',
            '[class*="result-box" i]',
            '[class*="result-card" i]',
            '[id*="report" i]',
            '[id*="output" i]'
        ];

        // بررسی از بیرونی‌ترین تگ به داخلی‌ترین (بدون reverse) تا کل کانتینر نتیجه گرفته شود
        const candidates = Array.from(toolWrap.querySelectorAll(selectors.join(',')));
        for (let i = 0; i < candidates.length; i++) {
            const el = candidates[i];
            if (el === toolWrap || el.classList.contains('madenna-tool-wrapper')) continue;
            if (el.getAttribute('data-initial-visible') === 'true') continue;
            if (el.getAttribute('data-vault-processed') === 'true') return;
            if (['BUTTON', 'SPAN', 'I', 'INPUT', 'LABEL', 'H1', 'H2', 'H3', 'H4', 'P', 'A'].includes(el.tagName)) continue;
            if (el.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea')) continue;

            const textLen = el.innerText.trim().length;
            if (isElementVisible(el) && textLen > 30 && el.offsetHeight > 50) {
                handleResultRevealed(resolveCompleteResultElement(el, toolWrap));
                return;
            }
        }

        const sections = Array.from(toolWrap.querySelectorAll('div, section'));
        for (let j = 0; j < sections.length; j++) {
            const sec = sections[j];
            if (sec === toolWrap || sec.classList.contains('madenna-tool-wrapper')) continue;
            if (sec.getAttribute('data-vault-processed') === 'true') continue;
            if (sec.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea')) continue;
            const txt = sec.innerText || '';
            if (
                (txt.includes('نتیجه ارزیابی') || txt.includes('نتیجه تست') || txt.includes('نمره شما') || txt.includes('تیپ شخصیتی شما') || txt.includes('تحلیل نهایی') || txt.includes('تست مجدد') || txt.includes('شروع مجدد')) &&
                sec.offsetHeight > 120
            ) {
                const hasRestartBtn = sec.querySelector('button');
                if (hasRestartBtn && (hasRestartBtn.innerText.includes('مجدد') || hasRestartBtn.innerText.includes('دوباره') || hasRestartBtn.innerText.includes('شروع'))) {
                    handleResultRevealed(resolveCompleteResultElement(sec, toolWrap));
                    return;
                }
            }
        }
    }

    function handleResultRevealed(element) {
        const toolWrap = getLiveToolContainer();
        if (!toolWrap || !element || element.getAttribute('data-vault-processed') === 'true') return;

        const effectiveToolId = getEffectiveToolId();
        if (!effectiveToolId || effectiveToolId <= 0) return;

        element = resolveCompleteResultElement(element, toolWrap);
        element.setAttribute('data-vault-processed', 'true');
        cachedResultElement = element;

        if (config.user_name) {
            autoPopulateToolUserFields(config.user_name);
        }

        if (config.is_logged_in) {
            setTimeout(() => {
                saveResultSnapshot(element);
            }, 550);
            return;
        }

        element.classList.add('mad-vault-blurred');
        openModal('tool');
    }

    function getEffectiveToolId() {
        if (document.querySelector('.mad-vault-dashboard-wrapper')) {
            return 0;
        }
        if (config.tool_id && parseInt(config.tool_id, 10) > 0) {
            return parseInt(config.tool_id, 10);
        }
        const wrapper = document.querySelector('[id^="madenna-tool-renderer-"], [id^="madenna-tool-"]');
        if (wrapper && wrapper.id) {
            const match = wrapper.id.match(/(\d+)$/);
            if (match && match[1]) {
                return parseInt(match[1], 10);
            }
        }
        return 0;
    }

    function extractToolInputsState() {
        const toolWrap = getLiveToolContainer();
        if (!toolWrap) return '';
        const inputs = {};
        toolWrap.querySelectorAll('input, select, textarea').forEach((el, idx) => {
            const key = el.id || el.name || ('field_' + idx);
            if (el.type === 'checkbox' || el.type === 'radio') {
                if (el.checked) inputs[key] = el.value;
            } else {
                inputs[key] = el.value;
            }
        });
        try {
            return JSON.stringify(inputs);
        } catch (e) {
            return '';
        }
    }

    function extractCleanSnapshotHtml(element) {
        if (!element) return '';
        const toolWrap = getLiveToolContainer();
        if (!toolWrap) return element.outerHTML;

        const wasBlurred = element.classList.contains('mad-vault-blurred');
        if (wasBlurred) {
            element.classList.remove('mad-vault-blurred');
        }

        element.setAttribute('data-vault-active-result', 'true');

        const origCanvases = toolWrap.querySelectorAll('canvas');
        origCanvases.forEach(origCvs => {
            if (origCvs.width > 0 && origCvs.height > 0) {
                try {
                    const dataUrl = origCvs.toDataURL('image/png');
                    origCvs.setAttribute('data-vault-canvas-state', dataUrl);
                    origCvs.setAttribute('data-vault-w', origCvs.width);
                    origCvs.setAttribute('data-vault-h', origCvs.height);
                } catch (e) {}
            }
            if (window.Chart && typeof window.Chart.getChart === 'function') {
                try {
                    const chartInst = window.Chart.getChart(origCvs);
                    if (chartInst && chartInst.config) {
                        const safeConfig = {
                            type: chartInst.config.type,
                            data: JSON.parse(JSON.stringify(chartInst.config.data || {})),
                            options: JSON.parse(JSON.stringify(chartInst.config.options || {}))
                        };
                        origCvs.setAttribute('data-vault-chart-config', JSON.stringify(safeConfig));
                    }
                } catch (e) {}
            }
        });

        const wrapClone = toolWrap.cloneNode(true);
        element.removeAttribute('data-vault-active-result');
        if (wasBlurred) {
            element.classList.add('mad-vault-blurred');
        }

        const origInputs  = toolWrap.querySelectorAll('input, select, textarea');
        const cloneInputs = wrapClone.querySelectorAll('input, select, textarea');
        origInputs.forEach((origInp, i) => {
            if (!cloneInputs[i]) return;
            if (origInp.type === 'checkbox' || origInp.type === 'radio') {
                if (origInp.checked) cloneInputs[i].setAttribute('checked', 'checked');
                else cloneInputs[i].removeAttribute('checked');
            } else if (origInp.tagName === 'INPUT') {
                cloneInputs[i].setAttribute('value', origInp.value || '');
            } else if (origInp.tagName === 'TEXTAREA') {
                cloneInputs[i].textContent = origInp.value || '';
            } else if (origInp.tagName === 'SELECT') {
                Array.from(cloneInputs[i].options).forEach((opt, oIdx) => {
                    if (origInp.options[oIdx] && origInp.options[oIdx].selected) {
                        opt.setAttribute('selected', 'selected');
                    } else {
                        opt.removeAttribute('selected');
                    }
                });
            }
        });

        wrapClone.querySelectorAll('a[href^="blob:"]').forEach(aEl => {
            const bUrl = aEl.getAttribute('href');
            if (bUrl && blobToDataUrlMap.has(bUrl)) {
                const dUrl = blobToDataUrlMap.get(bUrl);
                aEl.setAttribute('href', dUrl);
                aEl.setAttribute('data-vault-file-uri', dUrl);
            }
        });

        const clonedResult = wrapClone.querySelector('[data-vault-active-result="true"]') || wrapClone;
        clonedResult.classList.remove('mad-vault-blurred', 'mad-vault-unlocked', 'hidden');
        if (clonedResult.style && clonedResult.style.display === 'none') {
            clonedResult.style.removeProperty('display');
        }

        let curr = clonedResult;
        while (curr && curr !== wrapClone) {
            const parent = curr.parentElement;
            if (!parent) break;

            parent.classList.remove('hidden', 'mad-vault-blurred');
            if (parent.style && parent.style.display === 'none') {
                parent.style.removeProperty('display');
            }

            let hidAnySibling = false;
            Array.from(parent.children).forEach(sib => {
                if (sib === curr) return;
                if (['STYLE', 'LINK', 'SCRIPT'].includes(sib.tagName)) return;

                const sibId  = (sib.id || '').toLowerCase();
                const sibCls = (typeof sib.className === 'string' ? sib.className : '').toLowerCase();
                const isModal = sibId.includes('modal') || sibCls.includes('modal') || sib.getAttribute('role') === 'dialog';

                if (isModal) {
                    sib.classList.add('hidden');
                    return;
                }

                // فقط بخش‌هایی که مخفی هستند یا فرم/سوال/هدرِ پیش از تست هستند مخفی شوند، نه ادامه کارنامه!
                const isAlreadyHidden = sib.classList.contains('hidden') || (sib.style && sib.style.display === 'none');
                const isInitialPreTest = sib.getAttribute('data-initial-visible') === 'true' && clonedResult.getAttribute('data-was-hidden-initially') === 'true';

                if (isAlreadyHidden || isNonResultScreenOrForm(sib) || isInitialPreTest) {
                    sib.setAttribute('data-vault-hidden-step', 'true');
                    sib.classList.add('hidden');
                    if (sib.style) {
                        sib.style.setProperty('display', 'none', 'important');
                    }
                    hidAnySibling = true;
                }
            });

            if (hidAnySibling) {
                const pCls = typeof parent.className === 'string' ? parent.className : '';
                if (pCls.includes('grid-cols-') || pCls.includes('flex-row') || pCls.includes('lg:grid') || pCls.includes('md:grid')) {
                    parent.setAttribute('data-vault-single-col', 'true');
                    parent.style.setProperty('display', 'block', 'important');
                }
            }

            curr = parent;
        }

        wrapClone.querySelectorAll(
            '#madenna-tool-inputs, [data-tool-inputs], #quiz-section, #questions-section, #question-screen, #quiz-screen, #start-screen, #welcome-screen, #intro-screen, #input-section, #inputs-section, #form-section, form'
        ).forEach(qEl => {
            if (!qEl.contains(clonedResult) && !clonedResult.contains(qEl) && !qEl.querySelector('#madenna-tool-result, [data-tool-result="true"]')) {
                qEl.setAttribute('data-vault-hidden-step', 'true');
                qEl.classList.add('hidden');
                if (qEl.style) {
                    qEl.style.setProperty('display', 'none', 'important');
                }
            }
        });

        wrapClone.setAttribute('data-vault-full-snapshot', 'true');
        return `<div data-vault-full-snapshot="true">${wrapClone.innerHTML}</div>`;
    }

    function openModal(context = 'header') {
        createModalMarkup();
        currentModalContext = context;
        const modal    = document.getElementById('madenna-vault-modal');
        const closeBtn = document.getElementById('mad-vault-close-btn');
        const badge    = document.getElementById('mad-vault-context-badge');
        const title    = document.getElementById('mad-vault-modal-title');
        const desc     = document.getElementById('mad-vault-modal-desc');

        if (context === 'tool') {
            if (closeBtn) closeBtn.style.display = 'none';
            badge.style.display = 'inline-flex';
            title.textContent = 'مشاهده نتیجه و ذخیره در پرونده';
            desc.textContent = 'تحلیل و پاسخ شما آماده است! برای باز شدن قفل نتیجه و ذخیره در پرونده شخصی، وارد شوید.';
        } else {
            if (closeBtn) closeBtn.style.display = 'flex';
            badge.style.display = 'none';
            title.textContent = 'ورود یا عضویت در مادنا';
            desc.textContent = 'برای دسترسی به پرونده، سوابق ابزارها، اعلانات و پیشنهادات اختصاصی وارد شوید.';
        }

        setAuthMode('register');
        switchStep('phone');
        modal.style.display = 'flex';
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('madenna-vault-modal');
        if (modal) {
            modal.classList.remove('is-open');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function switchStep(step) {
        const stepPhone = document.getElementById('mad-vault-step-phone');
        const stepCode  = document.getElementById('mad-vault-step-code');
        clearErrors();

        if (step === 'phone') {
            stepPhone.style.display = 'block';
            stepCode.style.display  = 'none';
        } else {
            stepPhone.style.display = 'none';
            stepCode.style.display  = 'block';
            document.getElementById('mad-vault-code-input')?.focus();
        }
    }

    function clearErrors() {
        ['mad-vault-phone-error', 'mad-vault-code-error', 'mad-vault-name-error'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = '';
        });
    }

    async function handleSendOtp() {
        const nameInput  = document.getElementById('mad-vault-name-input');
        const phoneInput = document.getElementById('mad-vault-mobile-input');
        const errName    = document.getElementById('mad-vault-name-error');
        const errPhone   = document.getElementById('mad-vault-phone-error');
        const sendBtn    = document.getElementById('mad-vault-send-otp-btn');

        clearErrors();

        if (currentAuthMode === 'register') {
            const name = nameInput.value.trim();
            if (name.length < 3) {
                errName.textContent = 'لطفاً نام و نام خانوادگی خود را وارد کنید.';
                nameInput.focus();
                return;
            }
        }

        let phone = phoneInput.value.trim().replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
        if (!/^09[0-9]{9}$/.test(phone)) {
            errPhone.textContent = 'شماره همراه معتبر ۱۱ رقمی وارد کنید.';
            phoneInput.focus();
            return;
        }

        userMobile = phone;
        setBtnLoading(sendBtn, true, 'در حال ارسال...');

        try {
            const res = await fetch(`${config.rest_url}send-otp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.nonce || ''
                },
                body: JSON.stringify({ mobile: phone, auth_mode: currentAuthMode })
            });

            const data = await res.json();
            setBtnLoading(sendBtn, false, 'دریافت کد تایید پیامکی');

            if (res.ok && data.success) {
                switchStep('code');
                startCountdown(data.expires_in || 120);
            } else {
                errPhone.textContent = data.message || 'خطا در ارسال پیامک.';
            }
        } catch (err) {
            setBtnLoading(sendBtn, false, 'دریافت کد تایید پیامکی');
            errPhone.textContent = 'خطای ارتباط با سرور.';
        }
    }

    async function handleVerifyOtp() {
        const codeInput = document.getElementById('mad-vault-code-input');
        const nameInput = document.getElementById('mad-vault-name-input');
        const errCode   = document.getElementById('mad-vault-code-error');
        const verifyBtn = document.getElementById('mad-vault-verify-btn');

        let code = codeInput.value.trim().replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
        if (code.length < 4) {
            errCode.textContent = 'کد ۴ رقمی را کامل وارد کنید.';
            codeInput.focus();
            return;
        }

        setBtnLoading(verifyBtn, true, 'در حال بررسی...');

        const effectiveToolId = getEffectiveToolId();
        let snapshotHtml = '';
        let inputsJson   = '';
        if (currentModalContext === 'tool' && cachedResultElement && effectiveToolId > 0) {
            const enteredName = nameInput.value.trim();
            if (enteredName) {
                autoPopulateToolUserFields(enteredName);
            }
            snapshotHtml = extractCleanSnapshotHtml(cachedResultElement);
            inputsJson   = extractToolInputsState();
        }

        try {
            const res = await fetch(`${config.rest_url}verify-otp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    mobile: userMobile,
                    code: code,
                    auth_mode: currentAuthMode,
                    full_name: currentAuthMode === 'register' ? nameInput.value.trim() : '',
                    gender: selectedGender,
                    tool_id: effectiveToolId,
                    category_slug: config.category_slug,
                    result_html: snapshotHtml,
                    inputs_json: inputsJson
                })
            });

            const data = await res.json();
            setBtnLoading(verifyBtn, false, 'تایید و ورود');

            if (res.ok && data.success) {
                config.is_logged_in = true;
                config.user_id = data.user_id;
                if (data.user_name) {
                    config.user_name = data.user_name;
                }
                if (data.gender) {
                    config.user_gender = data.gender;
                }
                if (data.birthdate) {
                    config.user_birthdate = data.birthdate;
                }
                autoPopulateToolUserFields();
                if (data.new_nonce) {
                    config.nonce = data.new_nonce;
                }

                updateHeaderAuthWidgetLive(data);
                closeModal();

                if (currentModalContext === 'tool' && cachedResultElement) {
                    cachedResultElement.classList.remove('mad-vault-blurred');
                    cachedResultElement.classList.add('mad-vault-unlocked');
                    if (data.saved_record_id && data.saved_record_id > 0) {
                        cachedResultElement.setAttribute('data-vault-saved', 'true');
                        updateHeaderNotificationBadge(data.unseen_count);
                    } else if (effectiveToolId > 0) {
                        await saveResultSnapshot(cachedResultElement);
                    }
                } else {
                    location.reload();
                }
            } else {
                errCode.textContent = data.message || 'کد واردشده نادرست است.';
            }
        } catch (e) {
            setBtnLoading(verifyBtn, false, 'تایید و ورود');
            errCode.textContent = 'خطا در ارتباط.';
        }
    }

    function updateHeaderAuthWidgetLive(userData) {
        const headerWraps = document.querySelectorAll('.mad-vault-header-auth-wrap');
        if (!headerWraps.length) return;

        const userName    = userData.user_name || 'کاربر مادنا';
        const gender      = userData.gender || selectedGender || 'male';
        const unseenCount = parseInt(userData.unseen_count, 10) || 0;
        const avatarSvg   = userData.avatar_svg || (gender === 'female' ? FEMALE_SVG : MALE_SVG);

        headerWraps.forEach(wrap => {
            const dashUrl   = wrap.getAttribute('data-dashboard-url') || '/my-vault/';
            const showBadge = wrap.getAttribute('data-show-badge') !== 'no';
            const isHidden  = unseenCount <= 0;
            const badgeHtml = showBadge
                ? `<span class="mad-vault-header-badge ${isHidden ? 'is-hidden' : ''}" style="display: ${isHidden ? 'none !important' : 'inline-flex'};">${isHidden ? '' : unseenCount}</span>`
                : '';

            wrap.innerHTML = `
                <a href="${dashUrl}" class="mad-vault-header-user-btn" title="مشاهده پرونده و سوابق">
                    ${badgeHtml}
                    <div class="mad-vault-header-avatar ${gender}">
                        ${avatarSvg}
                    </div>
                    <span class="mad-user-name-txt" style="font-size:13px; font-weight:800;">${userName}</span>
                </a>
            `;
        });
    }

    function startCountdown(seconds) {
        clearInterval(countdownInterval);
        const resendBtn = document.getElementById('mad-vault-resend-btn');
        const timerText = document.getElementById('mad-vault-timer-text');

        resendBtn.style.display = 'none';
        timerText.style.display = 'inline';

        let remaining = seconds;
        updateTimer(remaining);

        countdownInterval = setInterval(() => {
            remaining--;
            updateTimer(remaining);
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                timerText.style.display = 'none';
                resendBtn.style.display = 'inline-block';
            }
        }, 1000);
    }

    function updateTimer(s) {
        const el = document.getElementById('mad-vault-countdown');
        if (!el) return;
        const m = Math.floor(s / 60);
        const sec = s % 60;
        el.textContent = `${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
    }

    async function saveResultSnapshot(element) {
        const effectiveToolId = getEffectiveToolId();
        if (!effectiveToolId || effectiveToolId <= 0) return;
        if (!element || element.getAttribute('data-vault-saved') === 'true') return;
        element.setAttribute('data-vault-saved', 'true');

        const snapshotHtml = extractCleanSnapshotHtml(element);
        const inputsJson   = extractToolInputsState();

        try {
            const fd = new FormData();
            fd.append('action', 'madenna_vault_save_result');
            fd.append('tool_id', effectiveToolId);
            fd.append('category_slug', config.category_slug || '');
            fd.append('user_id', config.user_id || 0);
            fd.append('result_html', snapshotHtml);
            fd.append('inputs_json', inputsJson);

            const ajaxUrl = config.ajax_url || '/wp-admin/admin-ajax.php';
            const res = await fetch(ajaxUrl, {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            });
            const json = await res.json();
            if (json && json.success && json.data) {
                updateHeaderNotificationBadge(json.data.unseen_count);
            }
        } catch (e) {}
    }

    function updateHeaderNotificationBadge(newCount) {
        const badges = document.querySelectorAll('.mad-vault-header-badge');
        badges.forEach(badge => {
            const count = newCount !== undefined ? parseInt(newCount, 10) : ((parseInt(badge.textContent, 10) || 0) + 1);
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('is-hidden');
                badge.style.display = 'inline-flex';
            } else {
                badge.textContent = '';
                badge.classList.add('is-hidden');
                badge.style.setProperty('display', 'none', 'important');
            }
        });
    }

    function setBtnLoading(btn, isLoading, text) {
        if (!btn) return;
        btn.disabled = isLoading;
        const span = btn.querySelector('span');
        if (span) span.textContent = text;
        btn.style.opacity = isLoading ? '0.75' : '1';
    }

    window.madennaOpenVaultModal = openModal;
    window.madennaVaultAvatars = { male: MALE_SVG, female: FEMALE_SVG };
})();