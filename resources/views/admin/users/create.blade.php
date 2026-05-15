@extends('layouts.app')

@section('title', 'مستخدم جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">المستخدمون</a></li>
    <li class="breadcrumb-item active">جديد</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-person-plus text-primary me-2"></i>مستخدم جديد
        </h4>
        <p class="text-muted small mb-0">إنشاء حساب جديد وتعيين دور له.</p>
    </div>

    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>رجوع إلى القائمة
    </a>
</div>

<div class="row g-4">

    {{-- ── العمود الرئيسي ── --}}
    <div class="col-lg-8">

        <form action="{{ route('admin.users.store') }}" method="POST" id="formCreateUser">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-fill me-2 text-primary"></i>المعلومات الشخصية
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            الاسم الكامل <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-person text-muted"></i>
                            </span>

                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="الاسم الكامل"
                                   required autofocus>
                        </div>

                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            البريد الإلكتروني <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>

                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="user@example.com"
                                   required>
                        </div>

                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-lock-fill me-2 text-warning"></i>الأمان والدور
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            كلمة المرور <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-key text-muted"></i>
                            </span>

                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="8 أحرف على الأقل"
                                   required
                                   oninput="checkStrength(this.value)">
                        </div>

                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        {{-- قوة كلمة المرور --}}
                        <div class="mt-2" id="strength-wrap" style="display:none">
                            <div class="d-flex gap-1 mb-1">
                                @for($i = 1; $i <= 4; $i++)
                                    <div class="flex-fill" style="height:4px;border-radius:2px;background:#e2e8f0" id="bar{{ $i }}"></div>
                                @endfor
                            </div>
                            <div class="small" id="strength-label" style="font-size:.75rem;color:#94a3b8"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            تأكيد كلمة المرور <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-key-fill text-muted"></i>
                            </span>

                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   class="form-control"
                                   placeholder="أعد كتابة كلمة المرور"
                                   required>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">
                            الدور <span class="text-danger">*</span>
                        </label>

                        <div class="row g-2 mt-1">
                            @foreach($roles as $role)
                            @php
                                $roleInfo = match($role->name) {
                                    'admin'   => ['warning', 'shield-fill-check', 'وصول كامل إلى جميع الميزات والإدارة.'],
                                    'manager' => ['primary', 'person-gear', 'إدارة الملفات والإجراءات والتقارير.'],
                                    default   => ['secondary', 'person', 'صلاحيات محدودة للعرض والاستخدام الأساسي.'],
                                };
                            @endphp

                            <div class="col-md-4">
                                <label class="card border cursor-pointer h-100 p-3 {{ old('role') === $role->name ? 'border-primary' : '' }}"
                                       style="cursor:pointer;transition:border-color .15s"
                                       for="role_{{ $role->name }}">

                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <input type="radio"
                                               name="role"
                                               id="role_{{ $role->name }}"
                                               value="{{ $role->name }}"
                                               class="form-check-input mt-0"
                                               @checked(old('role') === $role->name)
                                               required>

                                        <i class="bi bi-{{ $roleInfo[1] }} text-{{ $roleInfo[0] }}"></i>
                                        <span class="fw-semibold small">{{ ucfirst($role->name) }}</span>
                                    </div>

                                    <p class="text-muted mb-0" style="font-size:.75rem;line-height:1.4">
                                        {{ $roleInfo[2] }}
                                    </p>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        @error('role')
                            <div class="text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>إلغاء
            </a>

            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i>إنشاء المستخدم
            </button>
        </div>

        </form>
    </div>

    {{-- ── العمود الجانبي ── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield-check me-2 text-success"></i>نصائح أمنية
                </h6>
            </div>

            <div class="card-body small">
                <ul class="ps-3 mb-0" style="line-height:2">
                    <li>استخدم على الأقل <strong>8 أحرف</strong></li>
                    <li>امزج بين <strong>الأحرف الكبيرة</strong> و<strong>الصغيرة</strong></li>
                    <li>أضف <strong>أرقامًا</strong> و<strong>رموزًا</strong></li>
                    <li>لا تعِد استخدام كلمات المرور القديمة</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

function checkStrength(val) {
    const wrap  = document.getElementById('strength-wrap');
    const label = document.getElementById('strength-label');
    const bars  = [1,2,3,4].map(i => document.getElementById('bar' + i));

    if (!val) { wrap.style.display = 'none'; return; }

    wrap.style.display = 'block';

    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    score = Math.min(4, score);

    const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
    const labels = ['ضعيف جدًا','ضعيف','متوسط','قوي'];

    bars.forEach((b, i) => {
        b.style.background = i < score ? colors[score - 1] : '#e2e8f0';
    });

    label.textContent = labels[score - 1] || 'قصير جدًا';
    label.style.color = score > 0 ? colors[score - 1] : '#94a3b8';
}

document.querySelectorAll('input[name="role"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('input[name="role"]').forEach(r => {
            r.closest('label').classList.remove('border-primary');
        });
        this.closest('label').classList.add('border-primary');
    });
});
</script>
@endpush