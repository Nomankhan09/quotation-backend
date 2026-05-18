<?php

namespace App\Http\Controllers;

use App\Events\TaskCreated;
use App\Models\Task;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function createTask(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'contact_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'status' => 'required|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|integer',
            'notes' => 'nullable|string',
            'notification_id' => 'nullable|string'
        ]);

        // Create a new task
        $task = Task::create([
            'contact_id' => $validatedData['contact_id'],
            'user_id' => auth()->id(),
            'title' => $validatedData['title'],
            'status' => $validatedData['status'],
            'due_date' => $validatedData['due_date'] ?? null,
            'priority' => $validatedData['priority'] ?? null,
            'notes' => $validatedData['notes'] ?? null,
            'notification_id' =>  $validatedData['notification_id'] ?? null,
        ]);

        // call event
        event(new TaskCreated($task));

        return response()->json(['status' => 201, 'message' => 'Task created successfully', 'task' => $task], 201);
    }

    public function getTasks()
    {
        $tasks = Task::with('contact', 'priority')->orderBy('id', 'desc')->get();
        return response()->json(['status' => 200, 'tasks' => $tasks], 200);
    }

    public function updateTask(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|integer',
            'notes' => 'nullable|string',
            'notification_id' => 'nullable|string',
            'contact_id' => 'nullable|integer',
        ]);
        Log::info($validatedData);
        // Find the task and ensure it belongs to the authenticated user
        $task = Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Update the task with validated data
        $task->update($validatedData);
        $task->load('contact');

        return response()->json(['status' => 200, 'message' => 'Task updated successfully', 'task' => $task], 200);
    }

    public function deleteTask($id)
    {
        $task =  Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $task->delete();

        return response()->json(['status' => 200, 'message' => 'Task deleted successfully', 'task' => $task]);
    }

    public function getTaskByLead($lead_id)
    {
        $user_id = auth()->id();
        $leadTask = Task::where('contact_id', $lead_id)
            ->orderBy('id', 'desc')
            ->get();
        return response()->json(['status' => 200, 'tasks' => $leadTask], 200);
    }

    public function todayTasks()
    {
        $tasks = Task::whereDate('created_at', Carbon::today())->get();

        return response()->json(['status' => 200, 'message' => 'Today tasks', 'tasks' => $tasks], 200);
    }

    public function getTaskStatus()
    {
        $task_status = TaskStatus::all();

        return response()->json([
            'status' => 200,
            'message' => 'All task status',
            'data' => $task_status
        ]);
    }

    public function getTaskPriority()
    {
        $task_priority = TaskPriority::all();

        return response()->json([
            'status' => 200,
            'message' => 'All task priority',
            'data' => $task_priority
        ]);
    }
}
