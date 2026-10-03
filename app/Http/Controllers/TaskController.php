<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::all();

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        // タスクを作成
        $data = array_merge($validated, [
            'user_id' => 1,  // 本来は認証ユーザーのIDが入る
            'status' => 'pending',
        ]);
        
        $task = Task::create($data);

        return (new TaskResource($task))
        ->additional(['message' => 'タスクを作成しました'])
        ->response()
        ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // タスクを取得
        $task = Task::where('user_id', 1)->find($id);

        // タスクが見つからない場合
        if (!$task) {
            return response()->json([
                'message' => 'タスクが見つかりません',
                'error_code' => 'TASK_NOT_FOUND'
            ], 404);
        }

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // タスクを取得
        $task = Task::where('user_id', 1)->find($id);

        // タスクが見つからない場合
        if (!$task) {
            return response()->json([
                'message' => 'タスクが見つかりません'
            ], 404);
        }

        // バリデーション
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        // タスクを更新
        $task->update($validated);

        return (new TaskResource($task))
        ->additional(['message' => 'タスクを更新しました']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // タスクを取得
        $task = Task::where('user_id', 1)->find($id);

        // タスクが見つからない場合
        if (!$task) {
            return response()->json([
                'message' => 'タスクが見つかりません'
            ], 404);
        }

        // タスクを削除
        $task->delete();

        return response()->json(null, 204);
    }
}
