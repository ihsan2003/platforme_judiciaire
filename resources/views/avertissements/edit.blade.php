@extends('layouts.app')

@section('title', 'تعديل الإنذار')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('avertissements.index') }}">الإنذارات</a></li>
    <li class="breadcrumb-item active">تعديل #{{ $avertissement->id }}</li>
@endsection

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-pencil-square text-warning me-2"></i>تعديل الإنذار #{{ $avertissement->id }}
            </h4>
            <p class="text-muted small mb-0">عدّل المعلومات ثم احفظ التغييرات.</p>
        </div>
        <a href="{{ route('avertissements.show', $avertissement) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>العودة
        </a>
    </div>

    <form action="{{ route('avertissements.update', $avertissement) }}" method="POST">
        @csrf
        @method('PUT')
        @include('avertissements._form')
    </form>
@endsection
