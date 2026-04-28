<?php

namespace App\Repositories;

use App\Contracts\JobRepositoryInterface;
use App\Models\Job;
use Illuminate\Database\Eloquent\Collection;

class JobRepository implements JobRepositoryInterface
{
    public function create(string $description): Job
    {
        return Job::create(['title' => null, 'description' => $description]);
    }

    public function find(int $id): ?Job
    {
        return Job::find($id);
    }

    public function findOrFail(int $id): Job
    {
        return Job::findOrFail($id);
    }

    public function latest(): Collection
    {
        return Job::latest()->get();
    }
}
