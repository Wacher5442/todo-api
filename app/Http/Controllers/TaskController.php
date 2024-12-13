<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index() {
        return response(auth()->user()->tasks);
    }

    public function store(Request $request) {
        try {
            $request->validate([
                'title' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'duration' => 'required|integer|min:1',
            ]);
    
            $task = auth()->user()->tasks()->create($request->all());
    
            return response($task, 201);
        } catch (\Exception $e) {
            return response(['error' => 'Failed to create task', 'message' => $e->getMessage()], 400);
        }
    }
    public function update(Request $request, Task $task) {
        try {
            $this->authorize('update', $task);
    
            $request->validate([
                'title' => 'sometimes|required',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'duration' => 'sometimes|integer|min:1',
            ]);
    
            $task->update($request->all());
    
            return response($task);
        } catch (\Exception $e) {
            return response(['error' => 'Failed to update task', 'message' => $e->getMessage()], 400);
        }
    }

    public function destroy(Task $task) {
        $this->authorize('delete', $task);
    
        $task->delete();
    
        return response(null, 204);
    }
}
