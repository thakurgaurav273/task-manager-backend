<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $projectId = $request->query('project_id');
        $query = Task::with(['project', 'assignee']);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if (!$user->isAdmin()) {
            $query->where('assignee_id', $user->id);
        }

        $tasks = $query->get();

        $formattedTasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date->format('Y-m-d'),
                'assignee_id' => $task->assignee_id,
                'project_id' => $task->project_id,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->name,
                    'email' => $task->assignee->email,
                ] : null,
                'project' => $task->project ? [
                    'id' => $task->project->id,
                    'name' => $task->project->name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedTasks,
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $task = Task::with(['project', 'assignee'])->findOrFail($id);

        if (!$user->isAdmin() && $task->assignee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date->format('Y-m-d'),
                'assignee_id' => $task->assignee_id,
                'project_id' => $task->project_id,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->name,
                    'email' => $task->assignee->email,
                ] : null,
                'project' => $task->project ? [
                    'id' => $task->project->id,
                    'name' => $task->project->name,
                ] : null,
            ],
        ]);
    }

    public function store(CreateTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'assignee_id' => $request->assignee_id,
            'project_id' => $request->project_id,
        ]);

        $task->load(['project', 'assignee']);
        $this->checkOverdueTasks();
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date->format('Y-m-d'),
                'assignee_id' => $task->assignee_id,
                'project_id' => $task->project_id,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->name,
                    'email' => $task->assignee->email,
                ] : null,
                'project' => $task->project ? [
                    'id' => $task->project->id,
                    'name' => $task->project->name,
                ] : null,
            ],
        ], 201);
    }

    public function update(UpdateTaskRequest $request, $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $user = $request->user();

        if ($task->status === 'OVERDUE' && $request->has('status')) {
            $newStatus = $request->status;
            
            if ($newStatus === 'IN_PROGRESS') {
                return response()->json([
                    'success' => false,
                    'message' => 'Overdue tasks cannot be moved back to IN_PROGRESS',
                ], 400);
            }

            if ($newStatus === 'DONE' && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can close overdue tasks',
                ], 403);
            }
        }

        $djangoUrl = env('DJANGO_API_URL', 'http://localhost:8001');
        try {
            $response = Http::post("{$djangoUrl}/api/tasks/{$id}/validate-status", [
                'current_status' => $task->status,
                'new_status' => $request->input('status', $task->status),
                'due_date' => $task->due_date->format('Y-m-d'),
                'is_admin' => $user->isAdmin(),
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => $response->json()['message'] ?? 'Status validation failed',
                ], 400);
            }
        } catch (\Exception $e) {
            // Django service is optional
        }

        $task->update($request->only(['title', 'description', 'status', 'priority', 'due_date']));
        $this->checkOverdueTasks();
        $task->load(['project', 'assignee']);
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date->format('Y-m-d'),
                'assignee_id' => $task->assignee_id,
                'project_id' => $task->project_id,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
                'assignee' => $task->assignee ? [
                    'id' => $task->assignee->id,
                    'name' => $task->assignee->name,
                    'email' => $task->assignee->email,
                ] : null,
                'project' => $task->project ? [
                    'id' => $task->project->id,
                    'name' => $task->project->name,
                ] : null,
            ],
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can delete tasks',
            ], 403);
        }

        $task->delete();
        $this->checkOverdueTasks();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    private function checkOverdueTasks(): void
    {
        $djangoUrl = env('DJANGO_API_URL', 'http://localhost:8001');
        
        try {
            Http::post("{$djangoUrl}/api/overdue/mark");
        } catch (\Exception $e) {
            // Django service is optional
        }
    }
}
