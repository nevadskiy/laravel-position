<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class CreateTest extends TestCase
{
    /**
     * @test
     */
    public function it_assigns_position_value_when_model_is_creating(): void
    {
        $category = CategoryFactory::new()->create();

        static::assertSame(0, $category->position);
    }

    /**
     * @test
     */
    public function it_creates_model_at_end_of_sequence(): void
    {
        CategoryFactory::new()->createMany(2);

        $category = CategoryFactory::new()->create();

        static::assertSame(2, $category->position);
    }

    /**
     * @test
     */
    public function it_executes_2_queries_to_create_model_at_end_of_sequence(): void
    {
        CategoryFactory::new()->createMany(2);

        Category::query()->getConnection()->enableQueryLog();

        CategoryFactory::new()->create();

        self::assertCount(2, Category::query()->getConnection()->getQueryLog());
    }

    /**
     * @test
     */
    public function it_does_not_override_position_value_if_it_is_set_already(): void
    {
        $category = CategoryFactory::new()
            ->position(15)
            ->create();

        static::assertSame(15, $category->position);
    }

    /**
     * @test
     */
    public function it_can_create_model_in_middle_of_sequence(): void
    {
        $categories = CategoryFactory::new()->createMany(2);

        $category = CategoryFactory::new()
            ->position(1)
            ->create();

        static::assertSame(1, $category->position);
        static::assertSame(0, $categories[0]->fresh()->position);
        static::assertSame(2, $categories[1]->fresh()->position);
    }

    /**
     * @test
     */
    public function it_can_create_model_at_start_of_sequence(): void
    {
        $categories = CategoryFactory::new()->createMany(2);

        $category = CategoryFactory::new()
            ->position(0)
            ->create();

        static::assertSame(0, $category->position);
        static::assertSame(1, $categories[0]->fresh()->position);
        static::assertSame(2, $categories[1]->fresh()->position);
    }

    /**
     * @test
     */
    public function it_can_create_models_at_custom_start_position(): void
    {
        $categories = CategoryFactory::new()
            ->using(CustomStartCategory::class)
            ->createMany(3);

        static::assertSame(1, $categories[0]->fresh()->position);
        static::assertSame(2, $categories[1]->fresh()->position);
        static::assertSame(3, $categories[2]->fresh()->position);
    }

    /**
     * @test
     */
    public function it_can_create_models_at_custom_start_position_in_reverse_order(): void
    {
        $categories = CategoryFactory::new()
            ->using(CustomStartReserveCategory::class)
            ->createMany(3);

        static::assertSame(3, $categories[0]->fresh()->position);
        static::assertSame(2, $categories[1]->fresh()->position);
        static::assertSame(1, $categories[2]->fresh()->position);
    }

    /**
     * @test
     */
    public function it_can_automatically_create_models_at_start_of_sequence(): void
    {
        $categories = CategoryFactory::new()
            ->using(ReverseCategory::class)
            ->createMany(3);

        static::assertSame(2, $categories[0]->fresh()->position);
        static::assertSame(1, $categories[1]->fresh()->position);
        static::assertSame(0, $categories[2]->fresh()->position);
    }

    /**
     * @test
     */
    public function it_can_create_model_at_pre_last_position(): void
    {
        $categories = CategoryFactory::new()->createMany(3);

        static::assertSame(0, $categories[0]->position);
        static::assertSame(1, $categories[1]->position);
        static::assertSame(2, $categories[2]->position);

        $category = CategoryFactory::new()
            ->position(-2)
            ->create();

        static::assertSame(0, $categories[0]->fresh()->position);
        static::assertSame(1, $categories[1]->fresh()->position);
        static::assertSame(3, $categories[2]->fresh()->position);
        static::assertSame(2, $category->fresh()->position);
    }

    /**
     * @test
     */
    public function it_can_create_models_with_negative_positions(): void
    {
        $categories = CategoryFactory::new()
            ->using(NegativeStartCategory::class)
            ->createMany(3);

        static::assertSame(-10, $categories[0]->fresh()->position);
        static::assertSame(-9, $categories[1]->fresh()->position);
        static::assertSame(-8, $categories[2]->fresh()->position);
    }
}

class ReverseCategory extends Category
{
    public function getNextPosition(): int
    {
        return 0;
    }
}

class CustomStartCategory extends Category
{
    public function getStartPosition(): int
    {
        return 1;
    }

    public function getNextPosition(): int
    {
        return 0;
    }
}

class CustomStartReserveCategory extends Category
{
    public function getStartPosition(): int
    {
        return 1;
    }

    public function getNextPosition(): int
    {
        return $this->getStartPosition();
    }
}

class NegativeStartCategory extends Category
{
    public function getStartPosition(): int
    {
        return -10;
    }
}
