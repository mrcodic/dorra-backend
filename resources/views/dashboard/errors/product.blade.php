@extends('layouts.contentLayoutMaster') {{-- Or change to your master layout --}}

@section('title', $title ?? 'Error')

@section('content')
    <div class="container py-5 d-flex flex-column align-items-center justify-content-center text-center">
        <div class="card shadow rounded-4 p-5" style="max-width: 600px;">
            <h1 class="display-4 text-danger mb-3">{{ $code ?? 'Error' }}</h1>
            <h2 class="h4 mb-3">{{ $title ?? 'Something went wrong' }}</h2>
            <p class="mb-4">{{ $message ?? 'You should create "T-shit Product".' }}</p>


        </div>
    </div>
@endsection
