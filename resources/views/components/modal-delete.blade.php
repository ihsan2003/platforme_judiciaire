{{-- 
    resources/views/components/modal-delete.blade.php
    ─────────────────────────────────────────────────────
    مكوّن نافذة تأكيد الحذف القابل لإعادة الاستخدام.

    مثال بسيط:
        <x-modal-delete
            :action="route('dossiers.destroy', $dossier)"
            trigger-label="حذف"
        />

    مثال كامل:
        <x-modal-delete
            :action="route('audiences.destroy', $audience)"
            modal-id="deleteAudience{{ $audience->id }}"
            trigger-label="حذف"
            trigger-class="btn btn-sm btn-outline-danger"
            trigger-icon="bi-trash"
            title="حذف الجلسة"
            :description="'جلسة بتاريخ ' . $audience->date_audience->format('d/m/Y')"
            warning="هذا الإجراء لا يمكن التراجع عنه."
            confirm-label="نعم، حذف"
        />

    الخصائص:
        action          (string)   — رابط عملية الحذف (DELETE)               [إجباري]
        modal-id        (string)   — معرف فريد للنافذة
        trigger-label   (string)   — نص زر الفتح           (افتراضي: "حذف")
        trigger-class   (string)   — كلاس CSS للزر
        trigger-icon    (string)   — أيقونة Bootstrap Icons
        title           (string)   — عنوان النافذة
        description     (string)   — وصف العنصر المستهدف
        warning         (string)   — رسالة التحذير
        confirm-label   (string)   — نص زر التأكيد
--}}

@props([
    'action',
    'modalId'      => 'deleteModal_' . \Illuminate\Support\Str::uuid(),
    'triggerLabel' => 'حذف',
    'triggerClass' => 'btn btn-sm btn-outline-danger',
    'triggerIcon'  => 'bi-trash',
    'title'        => 'تأكيد الحذف',
    'description'  => null,
    'warning'      => 'هذا الإجراء نهائي ولا يمكن استرجاع البيانات بعد الحذف.',
    'confirmLabel' => 'نعم، حذف',
])

{{-- ── زر فتح النافذة ───────────────────────────────────── --}}
<button type="button"
        class="{{ $triggerClass }}"
        data-bs-toggle="modal"
        data-bs-target="#{{ $modalId }}">
    <i class="bi {{ $triggerIcon }}"></i>

    @if($triggerLabel)
        <span class="ms-1">{{ $triggerLabel }}</span>
    @endif
</button>

{{-- ── النافذة المنبثقة ───────────────────────────────── --}}
<div class="modal fade"
     id="{{ $modalId }}"
     tabindex="-1"
     aria-labelledby="{{ $modalId }}_label"
     aria-hidden="true"
     dir="rtl">

    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <div class="modal-content border-0 overflow-hidden"
             style="border-radius:16px; box-shadow:0 24px 64px rgba(0,0,0,.18)">

            {{-- شريط علوي أحمر --}}
            <div style="height:4px; background:linear-gradient(90deg,#dc3545 0%,#ff6b6b 100%)"></div>

            {{-- الرأس --}}
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-3 w-100">

                    {{-- الأيقونة --}}
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3"
                         style="width:52px;height:52px;background:#fff1f1;border:2px solid #ffd0d0">
                        <i class="bi bi-exclamation-triangle-fill"
                           style="font-size:1.4rem;color:#dc3545"></i>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <h5 class="modal-title fw-bold mb-0"
                            id="{{ $modalId }}_label"
                            style="color:#1a3a5c;font-size:1.05rem">
                            {{ $title }}
                        </h5>

                        @if($description)
                            <div class="text-muted small mt-1 text-truncate"
                                 title="{{ $description }}">
                                <i class="bi bi-tag me-1 opacity-50"></i>
                                {{ $description }}
                            </div>
                        @endif
                    </div>

                    <button type="button"
                            class="btn-close ms-auto flex-shrink-0"
                            data-bs-dismiss="modal"
                            aria-label="إغلاق"></button>
                </div>
            </div>

            {{-- المحتوى --}}
            <div class="modal-body px-4 pt-3 pb-2">

                @if($warning)
                    <div class="d-flex align-items-start gap-2 rounded-3 px-3 py-2 mb-1"
                         style="background:#fff8f0;border:1px solid #ffe4c4">

                        <i class="bi bi-shield-exclamation flex-shrink-0 mt-1"
                           style="color:#e07b00;font-size:.9rem"></i>

                        <p class="mb-0 small"
                           style="color:#7c3a00;line-height:1.5">
                            {{ $warning }}
                        </p>
                    </div>
                @endif

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
                <form action="{{ $action }}" method="POST" class="flex-fill">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn btn-danger w-100"
                            style="border-radius:10px;
                                   font-size:.88rem;
                                   padding:.55rem 1rem;
                                   background:linear-gradient(135deg,#dc3545 0%,#c82333 100%);
                                   border:none;
                                   box-shadow:0 4px 12px rgba(220,53,69,.35)">

                        <i class="bi bi-trash3-fill me-1"></i>
                        {{ $confirmLabel }}
                    </button>
                </form>

            </div>

        </div>
    </div>
</div>