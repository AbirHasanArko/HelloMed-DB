<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\QnaQuestion;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QnaController extends Controller
{
    public function index(Request $request): View
    {
        $page = $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $params = [
            'limit' => $perPage,
            'offset' => $offset,
            'total' => null
        ];

        $questionsCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_qna_questions(:limit, :offset, :total, :cursor); END;", $params, \App\Models\QnaQuestion::class);
        $total = $params['total'];

        foreach ($questionsCollection as $question) {
            $user = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $question->user_id], \App\Models\User::class)->first();
            $question->setRelation('user', $user);
            
            $answers = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_qna_answers(:question_id, :cursor); END;", ['question_id' => $question->id], \App\Models\QnaAnswer::class);
            foreach ($answers as $answer) {
                $ansUser = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $answer->user_id], \App\Models\User::class)->first();
                $answer->setRelation('user', $ansUser);
            }
            $question->setRelation('answers', $answers);
        }

        $questions = new \Illuminate\Pagination\LengthAwarePaginator($questionsCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('qna.index', compact('questions'));
    }

    public function show($id): View
    {
        $question = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_qna_question_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\QnaQuestion::class)->firstOrFail();
        
        $user = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $question->user_id], \App\Models\User::class)->first();
        $question->setRelation('user', $user);
        
        $answers = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_qna_answers(:question_id, :cursor); END;", ['question_id' => $question->id], \App\Models\QnaAnswer::class);
        foreach ($answers as $answer) {
            $ansUser = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $answer->user_id], \App\Models\User::class)->first();
            $answer->setRelation('user', $ansUser);
        }
        $question->setRelation('answers', $answers);

        return view('qna.show', compact('question'));
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === 'patient', 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'question' => ['required', 'string', 'max:5000'],
        ]);

        $params = [
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'question' => $validated['question'],
            'status' => 'open',
            'id' => null
        ];

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_qna_question(:user_id, :title, :question, :status, :id); END;", $params);

        $question = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_qna_question_by_id(:id, :cursor); END;", ['id' => $params['id']], \App\Models\QnaQuestion::class)->firstOrFail();

        AuditLogger::log('qna.question_created', $question, [], ['status' => 'open']);

        return redirect()->route('qna.show', $question)->with('status', 'Question posted successfully.');
    }

    public function storeAnswer(Request $request, $id): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['doctor', 'staff', 'admin'], true), 403);

        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:5000'],
        ]);

        $question = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_qna_question_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\QnaQuestion::class)->firstOrFail();

        $params = [
            'question_id' => $question->id,
            'user_id' => $request->user()->id,
            'answer' => $validated['answer'],
            'is_official' => 1,
            'id' => null
        ];

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_qna_answer(:question_id, :user_id, :answer, :is_official, :id); END;", $params);

        if ($question->status !== 'answered') {
            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_question_status(:id, :status); END;", ['id' => $question->id, 'status' => 'answered']);
            $question->status = 'answered';
        }

        AuditLogger::log('qna.answer_created', $question, [], [
            'answer_id' => $params['id'],
            'status' => $question->status,
        ]);

        return back()->with('status', 'Answer submitted successfully.');
    }
}
