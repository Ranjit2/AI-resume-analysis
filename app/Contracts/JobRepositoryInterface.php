<?php

namespace App\Contracts;

use App\Models\Job;
use Illuminate\Database\Eloquent\Collection;

interface JobRepositoryInterface
{
    public function create(string $description): Job;

    public function find(int $id): ?Job;

    public function findOrFail(int $id): Job;

    public function latest(): Collection;
}
