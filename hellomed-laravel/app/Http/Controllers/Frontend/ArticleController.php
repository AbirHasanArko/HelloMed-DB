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
                if (is_object($v) && (get_class($v) === 'OCILob' || get_class($v) === 'OCI-Lob' || method_exists($v, 'load'))) {
                    $v = $v->load();
                }
                $lowerRow[strtolower($k)] = $v;
            }
            $results[] = $lowerRow;
        }

        $hydrated = Article::hydrate($results);
        foreach ($hydrated as $article) {
            $category = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_article_categories(:cursor); END;", [], \App\Models\ArticleCategory::class)->where('id', $article->category_id)->first();
            $article->setRelation('category', $category);

            $author = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $article->user_id], \App\Models\User::class)->first();
            if ($author) {
                $authorProfile = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_user_id(:user_id, :cursor); END;", ['user_id' => $author->id], \App\Models\Doctor::class)->first();
                $author->setRelation('doctorProfile', $authorProfile);
            }
            $article->setRelation('author', $author);
        }

        $articles = new \Illuminate\Pagination\LengthAwarePaginator(
            $hydrated,
            $totalCount,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('articles.index', compact('articles'));
    }

    public function show(\App\Models\Article $article)
    {
        abort_unless($article->is_published, 404);

        $category = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_article_categories(:cursor); END;", [], \App\Models\ArticleCategory::class)->where('id', $article->category_id)->first();
        $article->setRelation('category', $category);

        $author = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $article->user_id], \App\Models\User::class)->first();
        if ($author) {
            $authorProfile = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_user_id(:user_id, :cursor); END;", ['user_id' => $author->id], \App\Models\Doctor::class)->first();
            $author->setRelation('doctorProfile', $authorProfile);
        }
        $article->setRelation('author', $author);

        $comments = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_article_comments(:article_id, :cursor); END;", ['article_id' => $article->id], \App\Models\ArticleComment::class);
        $totalRating = 0;
        $ratingCount = 0;
        foreach ($comments as $comment) {
            $commentUser = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $comment->user_id], \App\Models\User::class)->first();
            $comment->setRelation('user', $commentUser);
            if ($comment->rating !== null) {
                $totalRating += $comment->rating;
                $ratingCount++;
            }
        }
        $article->setRelation('comments', $comments);
        
        $averageRating = $ratingCount > 0 ? round((float) ($totalRating / $ratingCount), 1) : 0;

        return view('articles.show', compact('article', 'averageRating'));
    }
}
