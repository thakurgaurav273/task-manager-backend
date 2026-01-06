<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $projects = Project::with(['members', 'tasks.assignee'])->get();
        } else {
            $projects = Project::whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })->with(['members', 'tasks'])->get();
        }

        $formattedProjects = $projects->map(function ($project) use ($user) {
            $tasks = $project->tasks;
            
            if (!$user->isAdmin()) {
                $tasks = $tasks->where('assignee_id', $user->id);
            }
            
            $stats = [
                'todo' => $tasks->where('status', 'TODO')->count(),
                'in_progress' => $tasks->where('status', 'IN_PROGRESS')->count(),
                'done' => $tasks->where('status', 'DONE')->count(),
                'overdue' => $tasks->where('status', 'OVERDUE')->count(),
                'total' => $tasks->count(),
            ];
            
            return [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
                'member_ids' => $project->members->pluck('id')->toArray(),
                'members' => $project->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                    ];
                }),
                'stats' => $stats,
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $formattedProjects,
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $project = Project::with(['members', 'tasks.assignee'])->findOrFail($id);

        if (!$user->isAdmin() && !$project->members->contains($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $tasks = $project->tasks;
        
        if (!$user->isAdmin()) {
            $tasks = $tasks->where('assignee_id', $user->id);
        }

        $stats = [
            'todo' => $tasks->where('status', 'TODO')->count(),
            'in_progress' => $tasks->where('status', 'IN_PROGRESS')->count(),
            'done' => $tasks->where('status', 'DONE')->count(),
            'overdue' => $tasks->where('status', 'OVERDUE')->count(),
            'total' => $tasks->count(),
        ];
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
                'member_ids' => $project->members->pluck('id')->toArray(),
                'members' => $project->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                    ];
                }),
                'stats' => $stats,
            ],
        ]);
    }

    public function store(CreateProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $request->user()->id,
        ]);

        if ($request->has('member_ids')) {
            $project->members()->attach($request->member_ids);
        }

        $project->load(['members', 'tasks']);

        $stats = [
            'todo' => 0,
            'in_progress' => 0,
            'done' => 0,
            'overdue' => 0,
            'total' => 0,
        ];
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
                'member_ids' => $project->members->pluck('id')->toArray(),
                'members' => $project->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                    ];
                }),
                'stats' => $stats,
            ],
        ], 201);
    }
}
