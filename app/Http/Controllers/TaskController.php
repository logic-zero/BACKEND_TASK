<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * store() → create new task under a project.
     */
    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(Task::statuses())],
        ]);

        $task = $project->tasks()->create([
            'title' => $data['title'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => $data['status'] ?? Task::STATUS_PENDING,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Task created.');
    }

    // updateStatus() → update a task’s status (pending → in-progress → completed).
    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(Task::statuses())],
            'advance' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('advance')) {
            // cycle states
            $map = [
                Task::STATUS_PENDING => Task::STATUS_IN_PROGRESS,
                Task::STATUS_IN_PROGRESS => Task::STATUS_COMPLETED,
                Task::STATUS_COMPLETED => Task::STATUS_COMPLETED, // stay completed
            ];
            $task->status = $map[$task->status] ?? Task::STATUS_PENDING;
        } else if ($request->filled('status')) {
            $task->status = $request->input('status');
        }

        $task->save();

        return back()->with('success', 'Task status updated.');
    }
    public function tasksByUser($userId, Request $request)
    {
        /**
        * Query Builder join
        *
        *   1. Select fields we care about from tasks and projects
        *   2. JOIN tasks → projects (to fetch the project name for each task)
        *   3. Filter tasks where "assigned_to = $userId"
        *   4. Exclude soft-deleted tasks and projects (where deleted_at IS NULL)
        *   5. Order results by due_date ASC
        */
        $tasks = DB::table('tasks')
            ->select(
                'tasks.id',
                'tasks.title',
                'tasks.status',
                'tasks.due_date',
                'projects.id as project_id',
                'projects.name as project_name'
            )
            ->join('projects', 'tasks.project_id', '=', 'projects.id') // join example
            ->where('tasks.assigned_to', $userId)
            ->whereNull('tasks.deleted_at')
            ->whereNull('projects.deleted_at')
            ->orderBy('tasks.due_date', 'asc')
            ->get();

        return Inertia::render('Tasks/ByUser', [
            'tasks' => $tasks,
            'user_id' => (int) $userId,
        ]);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return back()->with('success', 'Task deleted.');
    }

}
