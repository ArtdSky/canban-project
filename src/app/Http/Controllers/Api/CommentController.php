<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Task;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Список комментариев к задаче
     *
     * @param Task $task
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Task $task, Request $request): JsonResponse
    {
        try {
            $comments = $this->commentService->getComments($task->id, $request->user()->id);

            return response()->json([
                'data' => $comments,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка.',
                'errors' => $e->errors(),
            ], 404);
        }
    }

    /**
     * Создание комментария
     *
     * @param StoreCommentRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function store(StoreCommentRequest $request, Task $task): JsonResponse
    {
        try {
            $comment = $this->commentService->createComment(
                $request->validated(),
                $task->id,
                $request->user()->id
            );

            return response()->json([
                'message' => 'Комментарий успешно создан.',
                'data' => $comment,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Просмотр комментария
     *
     * @param Comment $comment
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Comment $comment, Request $request): JsonResponse
    {
        try {
            $comment = $this->commentService->getComment($comment->id, $request->user()->id);

            return response()->json([
                'data' => $comment,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка.',
                'errors' => $e->errors(),
            ], 404);
        }
    }

    /**
     * Обновление комментария
     *
     * @param UpdateCommentRequest $request
     * @param Comment $comment
     * @return JsonResponse
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        try {
            $comment = $this->commentService->updateComment($comment->id, $request->validated(), $request->user()->id);

            return response()->json([
                'message' => 'Комментарий успешно обновлен.',
                'data' => $comment,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Удаление комментария
     *
     * @param Comment $comment
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Comment $comment, Request $request): JsonResponse
    {
        try {
            $this->commentService->deleteComment($comment->id, $request->user()->id);

            return response()->json([
                'message' => 'Комментарий успешно удален.',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка.',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
