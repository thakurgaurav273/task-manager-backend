<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $taskId = $this->route('id');
        $task = \App\Models\Task::find($taskId);
        $user = auth()->user();
        
        if (!$task || !$user) {
            return false;
        }
        
        return $user->isAdmin() || $task->assignee_id === $user->id;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:TODO,IN_PROGRESS,DONE,OVERDUE',
            'priority' => 'sometimes|in:LOW,MEDIUM,HIGH,CRITICAL',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'due_date' => 'sometimes|date',
        ];
    }
}
