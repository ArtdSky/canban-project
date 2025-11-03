<?php

namespace Tests\Unit\Services;

use App\Repositories\CommentRepository;
use App\Repositories\TaskRepository;
use App\Services\CommentService;
use App\StateMachines\CommentStatusStateMachine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

/**
 * @phpstan-ignore-next-line
 */
class CommentServiceTest extends TestCase
{
    protected CommentService $commentService;
    /**
     * @var CommentRepository&\Mockery\MockInterface
     */
    protected $commentRepository;
    /**
     * @var TaskRepository&\Mockery\MockInterface
     */
    protected $taskRepository;
    protected CommentStatusStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentRepository = Mockery::mock(CommentRepository::class);
        $this->taskRepository = Mockery::mock(TaskRepository::class);
        $this->stateMachine = new CommentStatusStateMachine();

        $this->commentService = new CommentService(
            $this->commentRepository,
            $this->taskRepository,
            $this->stateMachine
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_get_comments_for_task(): void
    {
        $taskId = 1;
        $userId = 1;

        $comments = new Collection([
            (object)['id' => 1, 'content' => 'Comment 1'],
            (object)['id' => 2, 'content' => 'Comment 2'],
        ]);

        $this->taskRepository
            ->shouldReceive('isUserParticipant')
            ->once()
            ->with($taskId, $userId)
            ->andReturn(true);

        $this->commentRepository
            ->shouldReceive('getCommentsByTaskId')
            ->once()
            ->with($taskId)
            ->andReturn($comments);

        $result = $this->commentService->getComments($taskId, $userId);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_throws_exception_when_user_not_participant_getting_comments(): void
    {
        $taskId = 1;
        $userId = 999;

        $this->taskRepository
            ->shouldReceive('isUserParticipant')
            ->once()
            ->with($taskId, $userId)
            ->andReturn(false);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Задача не найдена или у вас нет доступа к ней.');

        $this->commentService->getComments($taskId, $userId);
    }

    public function test_throws_exception_when_no_access_to_comment(): void
    {
        $commentId = 1;
        $userId = 999;
        $taskId = 1;

        $comment = $this->createMockComment($commentId, $taskId, 1, 'Test comment');

        $this->commentRepository
            ->shouldReceive('findOrFail')
            ->once()
            ->with($commentId)
            ->andReturn($comment);

        $this->taskRepository
            ->shouldReceive('isUserParticipant')
            ->once()
            ->with($taskId, $userId)
            ->andReturn(false);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Комментарий не найден или у вас нет доступа к нему.');

        $this->commentService->getComment($commentId, $userId);
    }

    public function test_throws_exception_when_updating_other_users_comment(): void
    {
        $commentId = 1;
        $userId = 999;
        $data = ['content' => 'Updated comment'];

        $comment = $this->createMockComment($commentId, 1, 1, 'Original comment');

        $this->commentRepository
            ->shouldReceive('findOrFail')
            ->once()
            ->with($commentId)
            ->andReturn($comment);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Вы можете редактировать только свои комментарии.');

        $this->commentService->updateComment($commentId, $data, $userId);
    }

    public function test_throws_exception_when_deleting_other_users_comment(): void
    {
        $commentId = 1;
        $userId = 999;

        $comment = $this->createMockComment($commentId, 1, 1, 'Comment');

        $this->commentRepository
            ->shouldReceive('findOrFail')
            ->once()
            ->with($commentId)
            ->andReturn($comment);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Вы можете скрывать только свои комментарии.');

        $this->commentService->deleteComment($commentId, $userId);
    }

    protected function createMockComment(int $id, int $taskId, int $userId, string $content)
    {
        $comment = Mockery::mock(\App\Models\Comment::class);

        // Мокируем установку атрибутов
        $comment->shouldAllowMockingProtectedMethods();
        $comment->shouldReceive('setAttribute')->andReturnSelf();

        $comment->shouldReceive('__get')->with('id')->andReturn($id);
        $comment->shouldReceive('__get')->with('task_id')->andReturn($taskId);
        $comment->shouldReceive('__get')->with('user_id')->andReturn($userId);
        $comment->shouldReceive('__get')->with('content')->andReturn($content);
        $comment->shouldReceive('__get')->with('status')->andReturn(CommentStatusStateMachine::STATUS_VISIBLE);

        $comment->shouldReceive('getAttribute')->with('id')->andReturn($id);
        $comment->shouldReceive('getAttribute')->with('task_id')->andReturn($taskId);
        $comment->shouldReceive('getAttribute')->with('user_id')->andReturn($userId);
        $comment->shouldReceive('getAttribute')->with('content')->andReturn($content);
        $comment->shouldReceive('getAttribute')->with('status')->andReturn(CommentStatusStateMachine::STATUS_VISIBLE);

        return $comment;
    }
}

