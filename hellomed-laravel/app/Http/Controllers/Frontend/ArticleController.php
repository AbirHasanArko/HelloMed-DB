<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = $request->filled('category') ? $request->input('category') : null;
        
        $perPage = 9;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page - 1) * $perPage;

        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_filters.filter_articles(:cat, :limit, :offset, :total, :cursor); END;');
        
        $stmt->bindParam(':cat', $categorySlug);
        $stmt->bindParam(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindParam(':total', $totalCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        
        $cursor = null;
        $stmt->bindParam(':cursor', $cursor, \PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($cursor);
        
        $results = [];
        while ($row = oci_fetch_assoc($cursor)) {
            $lowerRow = [];
            foreach ($row as $k => $v) {
                $lowerRow[strtolower($k)] = $v;
            }
            $results[] = $lowerRow;
        }

        $hydrated = Article::hydrate($results);
        $hydrated->load(['category', 'author.doctorProfile']);

        $articles = new \Illuminate\Pagination\LengthAwarePaginator(
            $hydrated,
            $totalCount,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('articles.index', compact('articles'));
    }

    public function show(Article $article)
    {
        abort_unless($article->is_published, 404);

        $article->load(['category', 'author.doctorProfile', 'comments.user']);
        $averageRating = round((float) $article->comments()->whereNotNull('rating')->avg('rating'), 1);

        return view('articles.show', compact('article', 'averageRating'));
    }
}
