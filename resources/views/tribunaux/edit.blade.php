@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier le tribunal</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('tribunaux.update', $tribunal) }}" method="POST">
        @csrf
        @method('PUT')

        @include('tribunaux._form')

    </form>
</div>
@endsection

