<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArticleCommentController extends Controller
{
    public function store(Request $request, $id): RedirectResponse
    {
        $article = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Article::class)->firstOrFail();
        abort_unless($article->is_published, 404);
        abort_unless($request->user()?->role === 'patient', 403);

        $validated = $request->validate([
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:3000'],
        ]);

        $params = [
            'article_id' => $article->id,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'] ?? null,
            'comment' => $validated['comment'],
            'id' => null
        ];
        
        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_article_comment(:article_id, :user_id, :rating, :comment, :id); END;", $params);

        $commentId = $params['id'];

        AuditLogger::log('article.comment_submitted', $article, [], [
            'comment_id' => $commentId,
            'rating' => $validated['rating'] ?? null,
        ]);

        return back()->with('status', 'Comment posted successfully.');
    }
}
