@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="nav-inner" style="padding: 0 0 16px;">
            <div>
                <h1>Articles</h1>
                <p>Manage hospital articles and general health content.</p>
            </div>
            <a class="button" href="{{ route('admin.articles.create') }}">New article</a>
        </div>
        <div class="card">
            @foreach ($articles as $article)
                <div class="list-item" style="margin-bottom: 12px;">
                    <h3>{{ $article->title }}</h3>
                    <p>{{ $article->category?->name }} · {{ $article->is_published ? 'Published' : 'Draft' }}</p>
                    <a class="ghost-button" href="{{ route('admin.articles.edit', $article) }}">Edit</a>
                </div>
            @endforeach
            {{ $articles->links() }}
        </div>
    </section>
@endsection
