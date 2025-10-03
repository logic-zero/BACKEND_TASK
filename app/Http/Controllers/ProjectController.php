<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim();

        // Load projects with their owner (user) using Eloquent ORM
        $projectsQuery = Project::with('user');

        if ($search) {
            // Apply search filter to projects by name if provided
            $projectsQuery->where('name', 'like', "%{$search}%");
        }

        // Fetch all projects (filtered and ordered)
        $projects = $projectsQuery->orderBy('created_at', 'desc')->get();

        /**
         * Query Builder Section
         *
         * This query:
         *   - Selects project_id from tasks
         *   - Counts all tasks per project (as "total")
         *   - Counts how many tasks are completed per project (as "completed")
         *   - Optionally joins "projects" if search filter is applied,
         *     so we only count tasks for matching projects
         *   - Ignores soft deleted tasks (where deleted_at IS NULL)
         *   - Groups results by project_id
         *   - Returns a collection keyed by project_id for easy lookup
         */
        $taskCounts = DB::table('tasks')
            ->select('project_id', DB::raw('count(*) as total'), DB::raw("SUM(status = 'completed') as completed"))
            ->when($search, function ($q) use ($search) {
                $q->join('projects', 'tasks.project_id', '=', 'projects.id')
                    ->where('projects.name', 'like', "%{$search}%");
            })
            ->whereNull('tasks.deleted_at')
            ->groupBy('project_id')
            ->get()
            ->keyBy('project_id');

        // Merge project data with aggregated task counts
        $projects = $projects->transform(function ($project) use ($taskCounts) {
            $counts = $taskCounts->get($project->id);
            return [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'owner' => $project->user ? [
                    'id' => $project->user->id,
                    'name' => $project->user->name,
                ] : null,
                'task_count' => $counts ? (int) $counts->total : 0,
                'completed_count' => $counts ? (int) $counts->completed : 0,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
            ];
        });

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $project = Project::create($data);

        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Project $project, Request $request)
    {
        //load tasks and their assigned users and comments
        $project->load(['tasks.assignedTo', 'tasks.comments.user', 'user']);

        /**
         *   - Query all project tasks, ordered by due_date
         *   - Map each task into a simplified array
         *   - Include assigned user info (id, name)
         *   - Include comments with commenter info
         */

        $tasks = $project->tasks()->with('assignedTo')->orderBy('due_date', 'asc')->get()->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'assigned_to' => $task->assignedTo ? [
                    'id' => $task->assignedTo->id,
                    'name' => $task->assignedTo->name,
                ] : null,
                'due_date' => $task->due_date,
                'comments' => $task->comments->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'comment' => $c->comment,
                        'user' => $c->user ? ['id' => $c->user->id, 'name' => $c->user->name] : null,
                        'created_at' => $c->created_at,
                    ];
                }),
            ];
        });

        return Inertia::render('Projects/Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'owner' => $project->user ? ['id' => $project->user->id, 'name' => $project->user->name] : null,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'tasks' => $tasks,
            ],
        ]);
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

}
