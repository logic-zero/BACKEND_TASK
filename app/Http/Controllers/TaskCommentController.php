<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $data = $request->validate([
            'comment' => ['required', 'string'],
        ]);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $data['comment'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Comment added',
                'comment' => $comment->load('user'),
            ], 201);
        }

        return back()->with('success', 'Comment added.');
    }
}
