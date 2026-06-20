<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Http\Requests\StoreArticleRequest;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AdminArticleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Article::class);

        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $params = [
            'limit' => $perPage,
            'offset' => $offset,
            'out_total' => null
        ];

        $articlesCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_admin_articles(:limit, :offset, :total, :cursor); END;", $params, \App\Models\Article::class);
        $total = \App\Helpers\OracleHelper::$lastOutParams['out_total'];

        foreach ($articlesCollection as $article) {
            $category = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_article_categories(:cursor); END;", [], \App\Models\ArticleCategory::class)->where('id', $article->category_id)->first();
            $article->setRelation('category', $category);

            $author = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $article->user_id], \App\Models\User::class)->first();
            $article->setRelation('author', $author);

            if ($article->reviewed_by_user_id) {
                $reviewer = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $article->reviewed_by_user_id], \App\Models\User::class)->first();
                $article->setRelation('reviewer', $reviewer);
            }
        }

        $articles = new \Illuminate\Pagination\LengthAwarePaginator($articlesCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('admin.articles.index', [
            'articles' => $articles,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Article::class);

        $categories = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_article_categories(:cursor); END;", [], \App\Models\ArticleCategory::class);

        return view('admin.articles.create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreArticleRequest $request)
    {
        $isPublished = $request->boolean('is_published');
        $validated = $request->validated();

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('article-covers', 'public');
        }

        $params = [
            'category_id' => $validated['article_category_id'],
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => \Illuminate\Support\Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'content' => $validated['body'],
            'cover_image_path' => $coverImagePath,
            'is_featured' => $request->boolean('is_featured') ? 1 : 0,
            'featured_order' => $request->integer('featured_order', 0),
            'is_published' => $isPublished ? 1 : 0,
            'publication_status' => $isPublished ? 'published' : 'draft',
            'reviewed_by_user_id' => $isPublished ? $request->user()->id : null,
            'reviewed_at' => $isPublished ? now()->format('Y-m-d H:i:s') : null,
            'id' => null
        ];

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_article(:category_id, :user_id, :title, :slug, :excerpt, :content, :cover_image_path, :is_featured, :featured_order, :is_published, :publication_status, :reviewed_by_user_id, TO_DATE(:reviewed_at, 'YYYY-MM-DD HH24:MI:SS'), :id); END;", $params);

        $article = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_by_id(:id, :cursor); END;", ['id' => $params['id']], \App\Models\Article::class)->firstOrFail();

        AuditLogger::log('article.created', clone $article, [], $article->only(['title', 'publication_status', 'is_published', 'is_featured']));

        return redirect()->route('admin.articles.index')->with('status', 'Article created.');
    }

    public function edit($id)
    {
        $article = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Article::class)->firstOrFail();
        $this->authorize('update', $article);

        $categories = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_article_categories(:cursor); END;", [], \App\Models\ArticleCategory::class);

        return view('admin.articles.edit', [
            'article' => $article,
            'categories' => $categories,
        ]);
    }

    public function update(StoreArticleRequest $request, $id)
    {
        $article = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Article::class)->firstOrFail();
        $this->authorize('update', $article);

        $old = $article->only(['title', 'publication_status', 'is_published', 'is_featured']);

        $isPublished = $request->boolean('is_published');
        $validated = $request->validated();

        $coverImagePath = $article->cover_image_path;
        if ($request->hasFile('cover_image')) {
            if (filled($article->cover_image_path)) {
                Storage::disk('public')->delete($article->cover_image_path);
            }
            $coverImagePath = $request->file('cover_image')->store('article-covers', 'public');
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_article(:id, :category_id, :title, :slug, :excerpt, :content, :cover_image_path, :is_featured, :featured_order, :is_published, :publication_status, :reviewed_by_user_id, TO_DATE(:reviewed_at, 'YYYY-MM-DD HH24:MI:SS'), TO_DATE(:published_at, 'YYYY-MM-DD HH24:MI:SS')); END;", [
            'id' => $article->id,
            'category_id' => $validated['article_category_id'],
            'title' => $validated['title'],
            'slug' => \Illuminate\Support\Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'content' => $validated['body'],
            'cover_image_path' => $coverImagePath,
            'is_featured' => $request->boolean('is_featured') ? 1 : 0,
            'featured_order' => $request->integer('featured_order', 0),
            'is_published' => $isPublished ? 1 : 0,
            'publication_status' => $isPublished ? 'published' : 'draft',
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now()->format('Y-m-d H:i:s'),
            'published_at' => null
        ]);

        $article = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_by_id(:id, :cursor); END;", ['id' => $article->id], \App\Models\Article::class)->firstOrFail();

        AuditLogger::log('article.updated', clone $article, $old, $article->only(['title', 'publication_status', 'is_published', 'is_featured']));

        return redirect()->route('admin.articles.index')->with('status', 'Article updated.');
    }

    public function review(\Illuminate\Http\Request $request, $id)
    {
        $article = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Article::class)->firstOrFail();
        $this->authorize('update', $article);

        $old = [
            'publication_status' => $article->publication_status,
            'is_published' => $article->is_published,
        ];

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
        ]);

        if ($validated['decision'] === 'approve') {
            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_article(:id, :category_id, :title, :slug, :excerpt, :content, :cover_image_path, :is_featured, :featured_order, :is_published, :publication_status, :reviewed_by_user_id, TO_DATE(:reviewed_at, 'YYYY-MM-DD HH24:MI:SS'), TO_DATE(:published_at, 'YYYY-MM-DD HH24:MI:SS')); END;", [
                'id' => $article->id,
                'category_id' => $article->category_id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'cover_image_path' => $article->cover_image_path,
                'is_featured' => $article->is_featured ? 1 : 0,
                'featured_order' => $article->featured_order,
                'is_published' => 1,
                'publication_status' => 'published',
                'reviewed_by_user_id' => $request->user()->id,
                'reviewed_at' => now()->format('Y-m-d H:i:s'),
                'published_at' => optional($article->published_at)->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s')
            ]);
            
            $article = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_by_id(:id, :cursor); END;", ['id' => $article->id], \App\Models\Article::class)->firstOrFail();

            AuditLogger::log('article.reviewed', clone $article, [
                ...$old,
            ], [
                'publication_status' => 'published',
                'is_published' => true,
            ], [
                'decision' => 'approve',
            ]);

            return back()->with('status', 'Article approved and published.');
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_article(:id, :category_id, :title, :slug, :excerpt, :content, :cover_image_path, :is_featured, :featured_order, :is_published, :publication_status, :reviewed_by_user_id, TO_DATE(:reviewed_at, 'YYYY-MM-DD HH24:MI:SS'), TO_DATE(:published_at, 'YYYY-MM-DD HH24:MI:SS')); END;", [
            'id' => $article->id,
            'category_id' => $article->category_id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'cover_image_path' => $article->cover_image_path,
            'is_featured' => $article->is_featured ? 1 : 0,
            'featured_order' => $article->featured_order,
            'is_published' => 0,
            'publication_status' => 'rejected',
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now()->format('Y-m-d H:i:s'),
            'published_at' => null
        ]);
        
        $article = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_by_id(:id, :cursor); END;", ['id' => $article->id], \App\Models\Article::class)->firstOrFail();

        AuditLogger::log('article.reviewed', clone $article, [
            ...$old,
        ], [
            'publication_status' => 'rejected',
            'is_published' => false,
        ], [
            'decision' => 'reject',
        ]);

        return back()->with('status', 'Article rejected. Doctor can edit and resubmit.');
    }
}
