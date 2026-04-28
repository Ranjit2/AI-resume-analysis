<?php

namespace App\Contracts;

use App\Models\ResumeAnalysis;

interface ResumeAnalysisRepositoryInterface
{
    public function create(int $jobId, array $data, string $resumePath): ResumeAnalysis;

    public function shortlist(int $id): void;
}
