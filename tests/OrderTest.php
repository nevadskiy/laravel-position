<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class OrderTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_order_models_by_position(): void
    {
        $category2 = CategoryFactory::new()
            ->position(2)
            ->create();

        $category0 = CategoryFactory::new()
            ->position(0)
            ->create();

        $category1 = CategoryFactory::new()
            ->position(1)
            ->create();

        $categories = Category::query()
            ->orderByPosition()
            ->get();

        static::assertTrue($categories[0]->is($category0));
        static::assertTrue($categories[1]->is($category1));
        static::assertTrue($categories[2]->is($category2));
    }

    /**
     * @test
     */
    public function it_can_order_models_by_reverse_position(): void
    {
        $category2 = CategoryFactory::new()
            ->position(2)
            ->create();

        $category0 = CategoryFactory::new()
            ->position(0)
            ->create();

        $category1 = CategoryFactory::new()
            ->position(1)
            ->create();

        $categories = Category::query()
            ->orderByReversePosition()
            ->get();

        static::assertTrue($categories[0]->is($category2));
        static::assertTrue($categories[1]->is($category1));
        static::assertTrue($categories[2]->is($category0));
    }

    /**
     * @test
     */
    public function it_can_be_ordered_by_position_by_default(): void
    {
        $category2 = CategoryFactory::new()
            ->position(2)
            ->create();

        $category0 = CategoryFactory::new()
            ->position(0)
            ->create();

        $category1 = CategoryFactory::new()
            ->position(1)
            ->create();

        $categories = OrderedCategory::query()->get();

        static::assertTrue($categories[0]->is($category0));
        static::assertTrue($categories[1]->is($category1));
        static::assertTrue($categories[2]->is($category2));
    }

    /**
     * @test
     */
    public function it_is_not_ordered_by_position_by_default(): void
    {
        $category2 = CategoryFactory::new()
            ->position(2)
            ->create();

        $category0 = CategoryFactory::new()
            ->position(0)
            ->create();

        $category1 = CategoryFactory::new()
            ->position(1)
            ->create();

        $categories = Category::query()->get();

        static::assertTrue($categories[0]->is($category2));
        static::assertTrue($categories[1]->is($category0));
        static::assertTrue($categories[2]->is($category1));
    }
}

class OrderedCategory extends Category
{
    public function alwaysOrderByPosition(): bool
    {
        return true;
    }
}
