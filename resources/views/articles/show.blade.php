@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="card">
            <div class="tag">{{ $article->category?->name }}</div>
            <h1>{{ $article->title }}</h1>
            <p>{{ $article->excerpt }}</p>
            <div class="muted">{{ $article->published_at?->format('M d, Y') }} · {{ $article->author?->name }}</div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <p>{!! nl2br(e($article->body)) !!}</p>
        </div>
    </section>
@endsection
