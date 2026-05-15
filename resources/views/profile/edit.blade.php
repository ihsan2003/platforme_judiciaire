@extends('layouts.app')

@section('title', 'ملفي الشخصي')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">ملفي الشخصي</li>
@endsection

@push('styles')
<style>
.profile-header {
    background: #1a3a5c;
    border-radius: 12px;
    padding: 28px 32px;
    color: #fff;
    margin-bottom: 24px;
}
.avatar-circle {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: rgba(200,168,75,.2);
    border: 2px solid rgba(200,168,75,.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    font-weight: 700;
    color: #c8a84b;
    flex-shrink: 0;
}
.profile-tab .nav-link {
    font-weight: 600;
    font-size: .85rem;
    color: #64748b;
    border: none;
    padding: .6rem 1.1rem;
    border-bottom: 3px solid transparent;
    border-radius: 0;
    transition: all .15s;
}
.profile-tab .nav-link.active {
    color: #1a3a5c;
    border-bottom-color: #1a3a5c;
    background: none;
}
.profile-tab .nav-link:hover:not(.active) {
    color: #1a3a5c;
    border-bottom-color: #e2e8f0;
    background: none;
}
.section-icon {
    width: 36px; height: 36px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.danger-zone {
    border: 1.5px solid #fee2e2;
    border-radius: 12px;
    background: #fff;
}
.danger-zone .card-header {
    background: #fff5f5;
    border-bottom: 1px solid #fee2e2;
    border-radius: 12px 12px 0 0 !important;
}
</style>
@endpush

@section('content')

@php
    $initials = collect(explode(' ', auth()->user()->name))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)
        ->implode('');
    $roles = auth()->user()->getRoleNames();
@endphp

{{-- ══ رأس الملف الشخصي ══ --}}
<div class="profile-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle">{{ $initials }}</div>
            <div>
                <h4 class="fw-bold mb-0 text-white">{{ auth()->user()->name }}</h4>
                <div class="small mt-1" style="opacity:.7">
                    <i class="bi bi-envelope me-1"></i>{{ auth()->user()->email }}
                </div>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    @foreach($roles as $role)
                        <span class="badge" style="background:rgba(200,168,75,.2);color:#c8a84b;border:1px solid rgba(200,168,75,.3);font-size:.72rem">
                            <i class="bi bi-shield-check me-1"></i>{{ ucfirst($role) }}
                        </span>
                    @endforeach
                    <span class="badge" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);font-size:.72rem">
                        <i class="bi bi-clock me-1"></i>عضو منذ {{ auth()->user()->created_at->format('M Y') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="text-end small" style="opacity:.6">
            <div><i class="bi bi-calendar-check me-1"></i>آخر تسجيل دخول : {{ now()->format('d/m/Y à H:i') }}</div>
        </div>
    </div>
</div>

{{-- ══ التبويبات ══ --}}
<ul class="nav profile-tab border-bottom mb-0" id="profileTabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-infos">
            <i class="bi bi-person me-1"></i>المعلومات
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-password">
            <i class="bi bi-lock me-1"></i>كلمة المرور
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm p-4" id="profileTabContent">

    {{-- ── التبويب 1: المعلومات ── --}}
    <div class="tab-pane fade show active" id="tab-infos">

        <div class="row gx-0 justify-content-between">
            <div class="col-lg-6">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="section-icon bg-primary bg-opacity-10">
                        <i class="bi bi-person-fill text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-semibold mb-0">المعلومات الشخصية</h6>
                        <div class="small text-muted">قم بتحديث اسمك والبريد الإلكتروني.</div>
                    </div>
                </div>

                @if(session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show small">
                        <i class="bi bi-check-circle me-2"></i>تم تحديث الملف الشخصي بنجاح.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')

                    <div class="row g-3">
                        <div class="col-12">
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
                                       value="{{ old('name', $user->name) }}"
                                       required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
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
                                       value="{{ old('email', $user->email) }}"
                                       required>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="alert alert-warning small mt-2 py-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    بريدك الإلكتروني غير مُفعّل.
                                    <form id="send-verification" method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm p-0 ms-1">
                                            إعادة إرسال رابط التفعيل
                                        </button>
                                    </form>
                                </div>
                                @if (session('status') === 'verification-link-sent')
                                    <div class="alert alert-success small mt-2 py-2">
                                        <i class="bi bi-check-circle me-1"></i>
                                        تم إرسال رابط تحقق جديد.
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>حفظ التعديلات
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            إلغاء
                        </a>
                    </div>
                </form>
            </div>

            {{-- ملخص الحساب --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-info-circle me-2 text-muted"></i>ملخص الحساب
                        </h6>
                    </div>
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted fw-normal">المعرف</dt>
                            <dd class="col-7 font-monospace">#{{ auth()->user()->id }}</dd>

                            <dt class="col-5 text-muted fw-normal">الدور</dt>
                            <dd class="col-7">
                                @foreach($roles as $role)
                                    <span class="badge bg-primary bg-opacity-10 text-primary d-block mb-1">{{ ucfirst($role) }}</span>
                                @endforeach
                            </dd>

                            <dt class="col-5 text-muted fw-normal">عضو منذ</dt>
                            <dd class="col-7">{{ auth()->user()->created_at->format('d/m/Y') }}</dd>

                            <dt class="col-5 text-muted fw-normal">آخر تحديث</dt>
                            <dd class="col-7">{{ auth()->user()->updated_at->format('d/m/Y') }}</dd>

                            <dt class="col-5 text-muted fw-normal">البريد مفعل</dt>
                            <dd class="col-7">
                                @if(auth()->user()->hasVerifiedEmail())
                                    <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25">
                                        <i class="bi bi-check-circle me-1"></i>نعم
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">لا</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── التبويب 2: كلمة المرور ── --}}
    <div class="tab-pane fade" id="tab-password">

        <div class="row gx-0 justify-content-between">
            <div class="col-lg-6">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="section-icon bg-warning bg-opacity-10">
                        <i class="bi bi-lock-fill text-warning"></i>
                    </div>
                    <div>
                        <h6 class="fw-semibold mb-0">تغيير كلمة المرور</h6>
                        <div class="small text-muted">استخدم كلمة مرور قوية وآمنة لحماية حسابك.</div>
                    </div>
                </div>

                @if(session('status') === 'password-updated')
                    <div class="alert alert-success alert-dismissible fade show small">
                        <i class="bi bi-check-circle me-2"></i>تم تحديث كلمة المرور بنجاح.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ url('password') }}">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                كلمة المرور الحالية <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password"
                                       name="current_password"
                                       id="current_password"
                                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                       autocomplete="current-password">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('current_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                كلمة المرور الجديدة <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-key text-muted"></i>
                                </span>
                                <input type="password"
                                       name="password"
                                       id="new_password"
                                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                       autocomplete="new-password"
                                       oninput="checkStrength(this.value)">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('new_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password', 'updatePassword')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <div class="mt-2" id="strength-wrap" style="display:none">
                                <div class="small" id="strength-label" style="color:#94a3b8;font-size:.75rem"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                تأكيد كلمة المرور الجديدة <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-key-fill text-muted"></i>
                                </span>
                                <input type="password"
                                       name="password_confirmation"
                                       id="confirm_password"
                                       class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('confirm_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password_confirmation', 'updatePassword')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-shield-lock me-1"></i>تحديث كلمة المرور
                        </button>
                    </div>
                </form>
            </div>

            {{-- نصائح الأمان --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-shield-check me-2 text-success"></i>نصائح الأمان
                        </h6>
                    </div>
                    <div class="card-body small">
                        <ul class="ps-3 mb-0" style="line-height:2">
                            <li>استخدم على الأقل <strong>8 أحرف</strong></li>
                            <li>امزج بين <strong>الأحرف الكبيرة والصغيرة</strong></li>
                            <li>أضف <strong>أرقامًا ورموزًا</strong></li>
                            <li>لا تستخدم اسمك أو تاريخ ميلادك</li>
                            <li>لا تعِد استخدام كلمات مرور قديمة</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection