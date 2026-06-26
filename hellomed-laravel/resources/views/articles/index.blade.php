@extends('layouts.app')
@section('title', 'Articles')

@section('content')
    <section class="section">
        <div class="fade-in" style="margin-bottom: 24px;">
            <h1>Articles</h1>
            <p>General hospital articles covering treatment guidance, prevention, departments, and patient education.</p>
            
            <div style="margin-top: 16px;">
                <form method="GET" action="{{ route('articles.index') }}" style="display: flex; gap: 8px;">
                    <select name="category" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; min-width: 200px;">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" @selected(request('category') == $cat->slug)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="button">Filter</button>
                    @if(request('category'))
                        <a href="{{ route('articles.index') }}" class="button" style="background-color: #eee; color: #333; border: 1px solid #ccc;">Clear</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="grid cols-3">
            @foreach ($articles as $article)
                <a class="card photo-card fade-in" href="{{ route('articles.show', $article) }}">
                    <div class="photo-card-img">
                        @if ($article->cover_image_path)
                            <img src="{{ Storage::url($article->cover_image_path) }}" alt="{{ $article->title }}" loading="lazy">
                        @else
                            <div style="width:100%;height:100%;background:linear-gradient(135deg, #0d9488, #6366f1);display:grid;place-items:center;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" opacity="0.4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            </div>
                        @endif
                        <div class="photo-card-overlay"></div>
                        <span class="photo-card-badge tag" style="margin-bottom:0;">{{ $article->category?->name }}</span>
                    </div>
                    <div class="photo-card-body">
                        <h3>{{ $article->title }}</h3>
                        <p>{{ $article->excerpt }}</p>
                        <div class="muted" style="font-size:12px;margin-top:auto;">
                            {{ $article->published_at?->format('M d, Y') }} ·
                            Writer: {{ $article->author?->doctorProfile?->name ?? $article->author?->name ?? 'HelloMed Team' }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div style="margin-top: 24px;">{{ $articles->links() }}</div>
    </section>
@endsection
