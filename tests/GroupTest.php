<?php

namespace Nevadskiy\Position\Tests;

use Nevadskiy\Position\Tests\App\Factories\BookFactory;
use Nevadskiy\Position\Tests\App\Factories\CategoryFactory;
use Nevadskiy\Position\Tests\App\Models\Category;

class GroupTest extends TestCase
{
    /**
     * @test
     */
    public function it_sets_positions_correctly_for_different_groups(): void
    {
        [$category, $anotherCategory] = CategoryFactory::new()->createMany(2);

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(2);

        $anotherBooks = BookFactory::new()
            ->forCategory($anotherCategory)
            ->createMany(2);

        static::assertSame(0, $books[0]->getPosition());
        static::assertSame(1, $books[1]->getPosition());
        static::assertSame(0, $anotherBooks[0]->getPosition());
        static::assertSame(1, $anotherBooks[1]->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_shift_positions_of_other_groups_on_create(): void
    {
        $category = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(2);

        $anotherBook = BookFactory::new()
            ->position(0)
            ->create();

        static::assertSame(0, $anotherBook->getPosition());
        static::assertSame(1, $books[1]->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_shift_positions_of_other_groups_on_update(): void
    {
        $category = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(2);

        $anotherBook = BookFactory::new()->create();

        static::assertSame(0, $books[0]->getPosition());
        static::assertSame(1, $books[1]->getPosition());
        static::assertSame(0, $anotherBook->getPosition());

        $books[1]->move(0);

        static::assertSame(0, $books[1]->fresh()->getPosition());
        static::assertSame(1, $books[0]->fresh()->getPosition());
        static::assertSame(0, $anotherBook->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_does_not_shift_positions_of_other_groups_on_delete(): void
    {
        $category = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(2);

        $anotherBook = BookFactory::new()
            ->position(2)
            ->create();

        $books[0]->delete();

        static::assertSame(0, $books[1]->fresh()->getPosition());
        static::assertSame(2, $anotherBook->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_syncs_positions_when_group_is_changed(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $book = BookFactory::new()
            ->forCategory($category)
            ->create();

        $anotherBook = BookFactory::new()
            ->forCategory($anotherCategory)
            ->create();

        $book->update([
            'category_id' => $anotherCategory->id,
        ]);

        static::assertSame(0, $book->fresh()->getPosition());
        static::assertSame(1, $anotherBook->fresh()->getPosition());
    }

    /**
     * @test
     */
    public function it_updates_group_along_with_position_correctly(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(3);

        $anotherBooks = BookFactory::new()
            ->forCategory($anotherCategory)
            ->createMany(3);

        $books[0]->update([
            'category_id' => $anotherCategory->id,
            'position' => 1,
        ]);

        static::assertSame(0, $books[1]->fresh()->position);
        static::assertSame(1, $books[2]->fresh()->position);

        static::assertSame(0, $anotherBooks[0]->fresh()->position);
        static::assertSame(1, $books[0]->fresh()->position);
        static::assertSame(2, $anotherBooks[1]->fresh()->position);
        static::assertSame(3, $anotherBooks[2]->fresh()->position);
    }

    /**
     * @test
     */
    public function it_moves_model_at_start_of_sequence_of_another_group(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(3);

        $anotherBooks = BookFactory::new()
            ->forCategory($anotherCategory)
            ->createMany(3);

        $books[0]->update([
            'category_id' => $anotherCategory->id,
            'position' => 0,
        ]);

        static::assertSame(0, $books[1]->fresh()->position);
        static::assertSame(1, $books[2]->fresh()->position);

        static::assertSame(0, $books[0]->fresh()->position);
        static::assertSame(1, $anotherBooks[0]->fresh()->position);
        static::assertSame(2, $anotherBooks[1]->fresh()->position);
        static::assertSame(3, $anotherBooks[2]->fresh()->position);
    }

    /**
     * @test
     */
    public function it_moves_model_at_end_of_sequence_of_another_group(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $books = BookFactory::new()
            ->forCategory($category)
            ->createMany(3);

        $anotherBooks = BookFactory::new()
            ->forCategory($anotherCategory)
            ->createMany(3);

        $books[0]->update([
            'category_id' => $anotherCategory->id,
            'position' => -1,
        ]);

        static::assertSame(0, $books[1]->fresh()->position);
        static::assertSame(1, $books[2]->fresh()->position);

        static::assertSame(0, $anotherBooks[0]->fresh()->position);
        static::assertSame(1, $anotherBooks[1]->fresh()->position);
        static::assertSame(2, $anotherBooks[2]->fresh()->position);
        static::assertSame(3, $books[0]->fresh()->position);
    }

    /**
     * @test
     */
    public function it_executes_3_queries_to_move_model_at_end_of_sequence_of_another_group(): void
    {
        $category = CategoryFactory::new()->create();
        $anotherCategory = CategoryFactory::new()->create();

        $book = BookFactory::new()
            ->forCategory($category)
            ->create();

        Category::query()->getConnection()->enableQueryLog();

        $book->update([
            'category_id' => $anotherCategory->id,
            'position' => -1,
        ]);

        self::assertCount(3, Category::query()->getConnection()->getQueryLog());
    }
}
