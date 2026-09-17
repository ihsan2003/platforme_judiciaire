{{--
    resources/views/components/confirm-modal.blade.php
    ─────────────────────────────────────────────────────
    نافذة تأكيد عامة تحل محل confirm() الأصلية للمتصفح، بنفس أسلوب
    <x-modal-delete>. تُدرَج مرة واحدة في layouts/app.blade.php وتُستخدم
    تلقائياً من طرف كل نموذج يحمل الخاصية data-confirm.

    مثال الاستعمال (بدل onsubmit="return confirm('...')"):

        <form action="..." method="POST"
              data-confirm="هل تريد حذف هذا العنصر؟"
              data-confirm-warning="هذا الإجراء لا يمكن التراجع عنه."
              data-confirm-variant="danger"
              data-confirm-label="نعم، حذف">
            @csrf
            <button type="submit">حذف</button>
        </form>

    الخصائص المدعومة على وسم <form>:
        data-confirm          (إجباري) — نص السؤال/التأكيد
        data-confirm-title    — عنوان النافذة (افتراضي: "تأكيد العملية")
        data-confirm-warning  — رسالة تحذير إضافية (اختياري)
        data-confirm-label    — نص زر التأكيد (افتراضي: "تأكيد")
        data-confirm-variant  — danger | warning | primary (افتراضي: danger)
--}}

<div class="modal fade"
     id="globalConfirmModal"
     tabindex="-1"
     aria-labelledby="globalConfirmModal_label"
     aria-hidden="true"
     dir="rtl">

    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <div class="modal-content border-0 overflow-hidden"
             style="border-radius:16px; box-shadow:0 24px 64px rgba(0,0,0,.18)">

            {{-- شريط علوي ملوّن (يتغيّر حسب variant) --}}
            <div id="globalConfirmModal_bar" style="height:4px; background:linear-gradient(90deg,#dc3545 0%,#ff6b6b 100%)"></div>

            {{-- الرأس --}}
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-3 w-100">

                    {{-- الأيقونة --}}
                    <div id="globalConfirmModal_iconWrap"
                         class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3"
                         style="width:52px;height:52px;background:#fff1f1;border:2px solid #ffd0d0">
                        <i id="globalConfirmModal_icon"
                           class="bi bi-exclamation-triangle-fill"
                           style="font-size:1.4rem;color:#dc3545"></i>
                    </div>

                    <div class="flex-grow-1 min-w-0" style="text-align:right !important; direction:rtl;">
                        <h5 class="modal-title fw-bold mb-0"
                            id="globalConfirmModal_label"
                            style="color:#1a3a5c;font-size:1.05rem">
                            تأكيد العملية
                        </h5>
                    </div>

                    <button type="button"
                            class="btn-close ms-auto flex-shrink-0"
                            data-bs-dismiss="modal"
                            aria-label="إغلاق"></button>
                </div>
            </div>

            {{-- المحتوى --}}
            <div class="modal-body px-4 pt-3 pb-2">

                <p id="globalConfirmModal_message"
                   class="mb-2"
                   style="color:#33475b;line-height:1.6;text-align:right;direction:rtl"></p>

                <div id="globalConfirmModal_warningWrap"
                     class="d-flex align-items-start gap-2 rounded-3 px-3 py-2 mb-1 d-none"
                     style="background:#fff8f0;border:1px solid #ffe4c4;text-align:right !important; direction:rtl;">

                    <i class="bi bi-shield-exclamation flex-shrink-0 mt-1"
                       style="color:#e07b00;font-size:.9rem"></i>

                    <p id="globalConfirmModal_warning"
                       class="mb-0 small"
                       style="color:#7c3a00;line-height:1.5"></p>
                </div>

            </div>

            {{-- التذييل --}}
            <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">

                {{-- زر الإلغاء --}}
                <button type="button"
                        class="btn btn-outline-secondary flex-fill"
                        data-bs-dismiss="modal"
                        style="border-radius:10px;font-size:.88rem;padding:.55rem 1rem">
                    <i class="bi bi-x-lg me-1"></i>
                    إلغاء
                </button>

                {{-- زر التأكيد --}}
                <button type="button"
                        id="globalConfirmModal_confirmBtn"
                        class="btn btn-danger flex-fill"
                        style="border-radius:10px;
                               font-size:.88rem;
                               padding:.55rem 1rem;
                               background:linear-gradient(135deg,#dc3545 0%,#c82333 100%);
                               border:none;
                               box-shadow:0 4px 12px rgba(220,53,69,.35)">
                    <i class="bi bi-check2 me-1"></i>
                    <span id="globalConfirmModal_confirmLabel">تأكيد</span>
                </button>

            </div>

        </div>
    </div>
</div>

<script>
(function () {
    var THEMES = {
        danger: {
            bar: 'linear-gradient(90deg,#dc3545 0%,#ff6b6b 100%)',
            iconBg: '#fff1f1', iconBorder: '#ffd0d0', iconColor: '#dc3545',
            btnBg: 'linear-gradient(135deg,#dc3545 0%,#c82333 100%)',
            btnShadow: '0 4px 12px rgba(220,53,69,.35)', icon: 'bi-exclamation-triangle-fill'
        },
        warning: {
            bar: 'linear-gradient(90deg,#e07b00 0%,#ffb04b 100%)',
            iconBg: '#fff8f0', iconBorder: '#ffe4c4', iconColor: '#e07b00',
            btnBg: 'linear-gradient(135deg,#e07b00 0%,#c86a00 100%)',
            btnShadow: '0 4px 12px rgba(224,123,0,.35)', icon: 'bi-exclamation-circle-fill'
        },
        primary: {
            bar: 'linear-gradient(90deg,#1a3a5c 0%,#2d5a8c 100%)',
            iconBg: '#eef4fb', iconBorder: '#c8ddf2', iconColor: '#1a3a5c',
            btnBg: 'linear-gradient(135deg,#1a3a5c 0%,#12283f 100%)',
            btnShadow: '0 4px 12px rgba(26,58,92,.35)', icon: 'bi-question-circle-fill'
        }
    };

    var modalEl      = document.getElementById('globalConfirmModal');
    var bar          = document.getElementById('globalConfirmModal_bar');
    var iconWrap     = document.getElementById('globalConfirmModal_iconWrap');
    var icon         = document.getElementById('globalConfirmModal_icon');
    var titleEl      = document.getElementById('globalConfirmModal_label');
    var messageEl    = document.getElementById('globalConfirmModal_message');
    var warningWrap  = document.getElementById('globalConfirmModal_warningWrap');
    var warningEl    = document.getElementById('globalConfirmModal_warning');
    var confirmBtn   = document.getElementById('globalConfirmModal_confirmBtn');
    var confirmLabel = document.getElementById('globalConfirmModal_confirmLabel');

    if (!modalEl || typeof bootstrap === 'undefined') return;

    var bsModal   = new bootstrap.Modal(modalEl);
    var activeForm = null;

    function applyTheme(name) {
        var t = THEMES[name] || THEMES.danger;
        bar.style.background        = t.bar;
        iconWrap.style.background   = t.iconBg;
        iconWrap.style.borderColor  = t.iconBorder;
        icon.style.color            = t.iconColor;
        icon.className              = 'bi ' + t.icon;
        confirmBtn.style.background = t.btnBg;
        confirmBtn.style.boxShadow  = t.btnShadow;
    }

    // اعتراض إرسال أي نموذج يحمل data-confirm، في طور الالتقاط (capture)
    // حتى يسبق أي معالج JS آخر مرتبط بنفس النموذج (كما في executions/create).
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.dataset.confirm) return;
        if (form.dataset.confirmed === 'true') return; // أُكِّد مسبقاً، اسمح بالإرسال الفعلي

        e.preventDefault();
        e.stopPropagation();

        activeForm = form;

        titleEl.textContent   = form.dataset.confirmTitle || 'تأكيد العملية';
        messageEl.textContent = form.dataset.confirm;
        confirmLabel.textContent = form.dataset.confirmLabel || 'تأكيد';
        applyTheme(form.dataset.confirmVariant || 'danger');

        if (form.dataset.confirmWarning) {
            warningEl.textContent = form.dataset.confirmWarning;
            warningWrap.classList.remove('d-none');
        } else {
            warningWrap.classList.add('d-none');
        }

        bsModal.show();
    }, true);

    confirmBtn.addEventListener('click', function () {
        if (!activeForm) return;
        var form = activeForm;
        bsModal.hide();
        form.dataset.confirmed = 'true';
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
        activeForm = null;
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        activeForm = null;
    });
})();
</script>