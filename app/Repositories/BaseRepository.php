<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    public function __construct(protected readonly Model $model)
    {
    }

    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function findOrFail(string $id): Model
    {
        return $this->query()->findOrFail($id);
    }
}
