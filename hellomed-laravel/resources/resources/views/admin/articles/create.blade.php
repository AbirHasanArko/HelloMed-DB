@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>Create article</h1>
        <div class="card">
            <form method="POST" action="{{ route('admin.articles.store') }}">
                @csrf
                <label>
                    Category
                    <select name="article_category_id" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Title
                    <input type="text" name="title" required>
                </label>
                <label>
                    Excerpt
                    <textarea name="excerpt" required></textarea>
                </label>
                <label>
                    Body
                    <textarea name="body" required></textarea>
                </label>
                <label>
                    <input type="checkbox" name="is_featured" value="1"> Featured
                </label>
                <label>
                    <input type="checkbox" name="is_published" value="1"> Published
                </label>
                <button class="button" type="submit">Save article</button>
            </form>
        </div>
    </section>
@endsection
