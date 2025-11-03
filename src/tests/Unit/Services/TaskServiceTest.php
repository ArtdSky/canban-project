<?php

namespace Tests\Unit\Services;

use App\Repositories\TaskRepository;
use App\Services\TaskService;
use App\StateMachines\TaskStatusStateMachine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

/**
 * @phpstan-ignore-next-line
 */
class TaskServiceTest extends TestCase
{
    protected TaskService $taskService;
    /**
     * @var TaskRepository&\Mockery\MockInterface
     */
    protected $taskRepository;
    protected TaskStatusStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем мок репозитория
        $this->taskRepository = Mockery::mock(TaskRepository::class);
        // State machine - обычный класс без зависимостей, создаем реальный экземпляр
        $this->stateMachine = new TaskStatusStateMachine();

        $this->taskService = new TaskService($this->taskRepository, $this->stateMachine);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_get_tasks_without_status_filter(): void
    {
        $userId = 1;
        $tasks = new Collection([
            (object)['id' => 1, 'title' => 'Task 1'],
            (object)['id' => 2, 'title' => 'Task 2'],
        ]);

        $this->taskRepository
            ->shouldReceive('getTasksByUserId')
            ->once()
            ->with($userId)
            ->andReturn($tasks);

        $result = $this->taskService->getTasks($userId);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_can_get_tasks_with_valid_status_filter(): void
    {
        $userId = 1;
        $status = TaskStatusStateMachine::STATUS_TODO;
        $tasks = new Collection([
            (object)['id' => 1, 'title' => 'Task 1'],
        ]);

        $this->taskRepository
            ->shouldReceive('getTasksByStatus')
            ->once()
            ->with($status, $userId)
            ->andReturn($tasks);

        $result = $this->taskService->getTasks($userId, $status);

        $this->assertCount(1, $result);
    }

    public function test_throws_exception_for_invalid_status(): void
    {
        $userId = 1;
        $status = 'invalid_status';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Недопустимый статус задачи.');

        $this->taskService->getTasks($userId, $status);
    }

    public function test_throws_exception_when_task_not_found(): void
    {
        $taskId = 999;
        $userId = 1;

        $this->taskRepository
            ->shouldReceive('findTaskForUser')
            ->once()
            ->with($taskId, $userId)
            ->andReturn(null);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Задача не найдена или у вас нет доступа к ней.');

        $this->taskService->getTask($taskId, $userId);
    }

    public function test_can_get_task_by_id(): void
    {
        $taskId = 1;
        $userId = 1;
        $task = $this->createMockTask($taskId, 'Test Task');

        $this->taskRepository
            ->shouldReceive('findTaskForUser')
            ->once()
            ->with($taskId, $userId)
            ->andReturn($task);

        $result = $this->taskService->getTask($taskId, $userId);

        $this->assertEquals($taskId, $result->id);
        $this->assertEquals('Test Task', $result->title);
    }

    public function test_throws_exception_for_invalid_status_transition_on_update(): void
    {
        $taskId = 1;
        $userId = 1;
        $task = $this->createMockTask($taskId, 'Task', TaskStatusStateMachine::STATUS_DONE);

        $this->taskRepository
            ->shouldReceive('findTaskForUser')
            ->once()
            ->with($taskId, $userId)
            ->andReturn($task);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Недопустимый переход статуса: из 'done' в 'todo'");

        $this->taskService->updateTask(
            $taskId,
            ['status' => TaskStatusStateMachine::STATUS_TODO],
            $userId
        );
    }

    public function test_throws_exception_for_invalid_role_when_adding_participant(): void
    {
        $taskId = 1;
        $userId = 1;
        $participantUserId = 2;
        $role = 'invalid_role';

        $task = $this->createMockTask($taskId, 'Task');
        $participants = Mockery::mock();

        $this->taskRepository
            ->shouldReceive('findTaskForUser')
            ->once()
            ->with($taskId, $userId)
            ->andReturn($task);

        $participants->shouldReceive('where')
            ->andReturnSelf();
        $participants->shouldReceive('exists')
            ->andReturn(false);

        $task->shouldReceive('participants')->andReturn($participants);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Недопустимая роль. Разрешены: creator, assignee, observer');

        $this->taskService->addParticipant($taskId, $userId, $participantUserId, $role);
    }

    public function test_throws_exception_when_removing_creator(): void
    {
        $taskId = 1;
        $userId = 1;
        $creatorId = 1;

        $task = $this->createMockTask($taskId, 'Task');

        $this->taskRepository
            ->shouldReceive('findTaskForUser')
            ->once()
            ->with($taskId, $userId)
            ->andReturn($task);

        $task->shouldReceive('getCreatorIdAttribute')->once()->andReturn($creatorId);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Нельзя удалить роль создателя задачи.');

        $this->taskService->removeParticipant($taskId, $userId, $creatorId, 'creator');
    }

    protected function createMockTask(int $id, string $title, string $status = TaskStatusStateMachine::STATUS_TODO)
    {
        $task = Mockery::mock(\App\Models\Task::class);

        // Мокируем установку атрибутов
        $task->shouldAllowMockingProtectedMethods();
        $task->shouldReceive('setAttribute')->andReturnSelf();

        $task->shouldReceive('__get')->with('id')->andReturn($id);
        $task->shouldReceive('__get')->with('title')->andReturn($title);
        $task->shouldReceive('__get')->with('status')->andReturn($status);
        $task->shouldReceive('getAttribute')->with('id')->andReturn($id);
        $task->shouldReceive('getAttribute')->with('title')->andReturn($title);
        $task->shouldReceive('getAttribute')->with('status')->andReturn($status);

        return $task;
    }
}

