@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ajouter un juge</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('juges.store') }}" method="POST">
        @csrf

        @include('juges._form')

    </form>
</div>
@endsection