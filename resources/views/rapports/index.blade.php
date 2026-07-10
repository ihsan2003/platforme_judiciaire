@extends('layouts.app')

@section('content')
<div class="container" dir="rtl">
    <h3 class="mb-4">توليد التقرير الإحصائي</h3>

    <form method="POST" action="{{ route('rapports.export') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">من تاريخ</label>
            <input type="date" name="date_debut" class="form-control" required value="{{ old('date_debut') }}">
        </div>
        <div class="mb-3">
            <label class="form-label">إلى تاريخ</label>
            <input type="date" name="date_fin" class="form-control" required value="{{ old('date_fin') }}">
        </div>
        @error('date_debut') <div class="text-danger">{{ $message }}</div> @enderror
        @error('date_fin') <div class="text-danger">{{ $message }}</div> @enderror

        <button type="submit" class="btn btn-primary">تحميل التقرير (Word)</button>
    </form>
</div>
@endsection
