<?php

namespace Nevadskiy\Position\Tests\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Position\HasPosition;

/**
 * @property int id
 * @property int position
 * @property int category_id
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Book extends Model
{
    use HasPosition;

    protected $table = 'books';

    public function newPositionQuery(): Builder
    {
        return $this->newQuery()->where('category_id', $this->category_id);
    }
}