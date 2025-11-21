<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\Avatar;
use Renfordt\AvatarSmithy\AvatarBuilder;

#[CoversClass(Avatar::class)]
#[CoversClass(AvatarBuilder::class)]
class AvatarTest extends TestCase
{
    public function test_engine_method_returns_avatar_builder(): void
    {
        $builder = Avatar::engine('initials');

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }

    public function test_for_method_returns_avatar_builder(): void
    {
        $builder = Avatar::for(['name' => 'John Doe', 'email' => 'john@example.com']);

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }

    public function test_for_method_with_object_with_properties(): void
    {
        $user = new class () {
            public string $name = 'Jane Smith';
            public string $email = 'jane@example.com';
        };

        $builder = Avatar::for($user);

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }

    public function test_for_method_with_object_with_methods(): void
    {
        $user = new class () {
            public function getName(): string
            {
                return 'Bob Johnson';
            }

            public function getEmail(): string
            {
                return 'bob@example.com';
            }
        };

        $builder = Avatar::for($user);

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }

    public function test_for_method_with_array_containing_name(): void
    {
        $user = ['name' => 'Alice Cooper'];

        $builder = Avatar::for($user);

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }

    public function test_for_method_with_array_containing_email(): void
    {
        $user = ['email' => 'alice@example.com'];

        $builder = Avatar::for($user);

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }

    public function test_for_method_with_empty_array(): void
    {
        $builder = Avatar::for([]);

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }

    public function test_for_method_with_object_without_name_or_email(): void
    {
        $user = new class () {
            public string $id = '123';
        };

        $builder = Avatar::for($user);

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }

    public function test_for_method_with_non_string_properties(): void
    {
        $user = new class () {
            public int $name = 123;
            public int $email = 456;
        };

        $builder = Avatar::for($user);

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }

    public function test_for_method_with_non_string_array_values(): void
    {
        $user = ['name' => 123, 'email' => 456];

        $builder = Avatar::for($user);

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }

    public function test_for_method_with_object_with_non_string_method_returns(): void
    {
        $user = new class () {
            public function getName(): int
            {
                return 123;
            }

            public function getEmail(): int
            {
                return 456;
            }
        };

        $builder = Avatar::for($user);

        $this->assertInstanceOf(AvatarBuilder::class, $builder);
    }
}
