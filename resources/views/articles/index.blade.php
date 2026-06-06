@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>Articles</h1>
        <p>General hospital articles covering treatment guidance, prevention, departments, and patient education.</p>
        <div class="grid cols-3">
            @foreach ($articles as $article)
                <a class="card" href="{{ route('articles.show', $article) }}">
                    <div class="tag">{{ $article->category?->name }}</div>
                    <h3>{{ $article->title }}</h3>
                    <p>{{ $article->excerpt }}</p>
                    <div class="muted">{{ $article->published_at?->format('M d, Y') }}</div>
                </a>
            @endforeach
        </div>
        <div style="margin-top: 20px;">{{ $articles->links() }}</div>
    </section>
@endsection
