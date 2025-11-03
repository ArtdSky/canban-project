<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Список задач с фильтрацией по статусу
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $status = $request->query('status');
            $tasks = $this->taskService->getTasks($request->user()->id, $status);

            return response()->json([
                'data' => $tasks,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Создание новой задачи
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $task = $this->taskService->createTask($request->validated(), $request->user()->id);

            return response()->json([
                'message' => 'Задача успешно создана.',
                'data' => $task,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Просмотр задачи
     *
     * @param \App\Models\Task $task
     * @param Request $request
     * @return JsonResponse
     */
    public function show(\App\Models\Task $task, Request $request): JsonResponse
    {
        try {
            $task = $this->taskService->getTask($task->id, $request->user()->id);

            return response()->json([
                'data' => $task,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка.',
                'errors' => $e->errors(),
            ], 404);
        }
    }

    /**
     * Обновление задачи
     *
     * @param UpdateTaskRequest $request
     * @param \App\Models\Task $task
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, \App\Models\Task $task): JsonResponse
    {
        try {
            $task = $this->taskService->updateTask($task->id, $request->validated(), $request->user()->id);

            return response()->json([
                'message' => 'Задача успешно обновлена.',
                'data' => $task,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Удаление задачи
     *
     * @param \App\Models\Task $task
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(\App\Models\Task $task, Request $request): JsonResponse
    {
        try {
            $this->taskService->deleteTask($task->id, $request->user()->id);

            return response()->json([
                'message' => 'Задача успешно удалена.',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка.',
                'errors' => $e->errors(),
            ], 404);
        }
    }
}
