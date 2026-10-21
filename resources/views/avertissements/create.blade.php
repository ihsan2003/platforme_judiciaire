@extends('layouts.app')

@section('title', 'إنذار جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('avertissements.index') }}">الإنذارات</a></li>
    <li class="breadcrumb-item active">إنذار جديد</li>
@endsection

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-exclamation-triangle text-warning me-2"></i>إضافة إنذار جديد
            </h4>
            <p class="text-muted small mb-0">قم بإدخال معلومات الإنذار بدقة لضمان المتابعة.</p>
        </div>
        <a href="{{ route('avertissements.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>العودة إلى القائمة
        </a>
    </div>

    <form action="{{ route('avertissements.store') }}" method="POST">
        @csrf
        @include('avertissements._form')
    </form>
@endsection
