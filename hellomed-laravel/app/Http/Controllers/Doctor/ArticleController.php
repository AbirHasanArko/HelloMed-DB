<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $params = [
            'user_id' => request()->user()->id,
            'limit' => $perPage,
            'offset' => $offset,
            'total' => null
        ];

        $articlesCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_doctor_articles(:user_id, :limit, :offset, :total, :cursor); END;", $params, \App\Models\Article::class);
        $total = $params['total'];

        foreach ($articlesCollection as $article) {
            $category = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_article_categories(:cursor); END;", [], \App\Models\ArticleCategory::class)->where('id', $article->category_id)->first();
            $article->setRelation('category', $category);

            if ($article->reviewed_by_user_id) {
                $reviewer = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $article->reviewed_by_user_id], \App\Models\User::class)->first();
                $article->setRelation('reviewer', $reviewer);
            }
        }

        $articles = new \Illuminate\Pagination\LengthAwarePaginator($articlesCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('doctor.articles.index', [
            'articles' => $articles,
        ]);
    }

    public function create(): View
    {
        $categories = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_article_categories(:cursor); END;", [], \App\Models\ArticleCategory::class);

        return view('doctor.articles.create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $status = $request->input('submit_action') === 'save_draft' ? 'draft' : 'pending_review';
        $validated = $request->validated();

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('article-covers', 'public');
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_article(:category_id, :user_id, :title, :slug, :excerpt, :content, :cover_image_path, :is_featured, :featured_order, :is_published, :publication_status, :reviewed_by_user_id, :reviewed_at, :id); END;", [
            'category_id' => $validated['category_id'],
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => \Illuminate\Support\Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'cover_image_path' => $coverImagePath,
            'is_featured' => 0,
            'featured_order' => 0,
            'is_published' => 0,
            'publication_status' => $status,
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'id' => null
        ]);

        return redirect()->route('doctor.articles.index')->with('status', $status === 'draft' ? 'Draft saved.' : 'Article submitted for review.');
    }

    public function edit($id): View
    {
        $article = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Article::class)->firstOrFail();
        abort_unless($article->user_id === request()->user()->id, 403);
        abort_if($article->publication_status === 'published', 403, 'Published article cannot be edited from doctor panel.');

        $categories = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_article_categories(:cursor); END;", [], \App\Models\ArticleCategory::class);

        return view('doctor.articles.edit', [
            'article' => $article,
            'categories' => $categories,
        ]);
    }

    public function update(StoreArticleRequest $request, $id): RedirectResponse
    {
        $article = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Article::class)->firstOrFail();
        abort_unless($article->user_id === $request->user()->id, 403);
        abort_if($article->publication_status === 'published', 403, 'Published article cannot be edited from doctor panel.');

        $status = $request->input('submit_action') === 'save_draft' ? 'draft' : 'pending_review';
        $validated = $request->validated();

        $coverImagePath = $article->cover_image_path;
        if ($request->hasFile('cover_image')) {
            if (filled($article->cover_image_path)) {
                Storage::disk('public')->delete($article->cover_image_path);
            }
            $coverImagePath = $request->file('cover_image')->store('article-covers', 'public');
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_article(:id, :category_id, :title, :slug, :excerpt, :content, :cover_image_path, :is_featured, :featured_order, :is_published, :publication_status, :reviewed_by_user_id, :reviewed_at, :published_at); END;", [
            'id' => $article->id,
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'slug' => \Illuminate\Support\Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'cover_image_path' => $coverImagePath,
            'is_featured' => 0,
            'featured_order' => 0,
            'is_published' => 0,
            'publication_status' => $status,
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'published_at' => null
        ]);

        return redirect()->route('doctor.articles.index')->with('status', $status === 'draft' ? 'Draft updated.' : 'Article submitted for review.');
    }
}
