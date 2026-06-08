@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>Edit article</h1>
        <div class="card">
            <form method="POST" action="{{ route('admin.articles.update', $article) }}">
                @csrf
                @method('PUT')
                <label>
                    Category
                    <select name="article_category_id" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($article->article_category_id === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Title
                    <input type="text" name="title" value="{{ $article->title }}" required>
                </label>
                <label>
                    Excerpt
                    <textarea name="excerpt" required>{{ $article->excerpt }}</textarea>
                </label>
                <label>
                    Body
                    <textarea name="body" required>{{ $article->body }}</textarea>
                </label>
                <label>
                    <input type="checkbox" name="is_featured" value="1" @checked($article->is_featured)> Featured
                </label>
                <label>
                    <input type="checkbox" name="is_published" value="1" @checked($article->is_published)> Published
                </label>
                <button class="button" type="submit">Update article</button>
            </form>
        </div>
    </section>
@endsection
