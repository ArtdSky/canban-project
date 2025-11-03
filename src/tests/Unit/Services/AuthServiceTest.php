<?php

namespace Tests\Unit\Services;

use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

/**
 * @phpstan-ignore-next-line
 */
class AuthServiceTest extends TestCase
{
    protected AuthService $authService;
    /**
     * @var UserRepository&\Mockery\MockInterface
     */
    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->authService = new AuthService($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_register_new_user(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $user = new \App\Models\User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->userRepository
            ->shouldReceive('emailExists')
            ->once()
            ->with('test@example.com')
            ->andReturn(false);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($user);

        $result = $this->authService->register($data);

        $this->assertInstanceOf(\App\Models\User::class, $result);
        $this->assertEquals('Test User', $result->name);
    }

    public function test_throws_exception_when_email_already_exists(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ];

        $this->userRepository
            ->shouldReceive('emailExists')
            ->once()
            ->with('existing@example.com')
            ->andReturn(true);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Пользователь с таким email уже существует.');

        $this->authService->register($data);
    }

    public function test_throws_exception_with_invalid_email(): void
    {
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('nonexistent@example.com')
            ->andReturn(null);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Неверные учетные данные.');

        $this->authService->login($credentials);
    }

    public function test_throws_exception_with_invalid_password(): void
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];

        $user = new \App\Models\User([
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('test@example.com')
            ->andReturn($user);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Неверные учетные данные.');

        $this->authService->login($credentials);
    }
}

