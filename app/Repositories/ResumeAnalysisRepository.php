<?php

namespace App\Repositories;

use App\Contracts\ResumeAnalysisRepositoryInterface;
use App\Models\ResumeAnalysis;

class ResumeAnalysisRepository implements ResumeAnalysisRepositoryInterface
{
    public function create(int $jobId, array $data, string $resumePath): ResumeAnalysis
    {
        return ResumeAnalysis::create([
            'job_id'              => $jobId,
            'candidate_name'      => $data['candidate_name'] ?? $data['name'] ?? null,
            'email'               => $data['email'] ?? null,
            'summary'             => $data['summary'] ?? null,
            'most_recent_role'    => $data['most_recent_role'] ?? null,
            'core_skills'         => $data['skills_matched'] ?? [],
            'missing_skills'      => $data['skills_missing'] ?? [],
            'skill_proficiency'   => $data['skill_proficiency'] ?? [],
            'work_experience'     => $data['work_experience'] ?? [],
            'interview_questions' => $data['interview_questions'] ?? [],
            'confidence_score'    => $data['confidence_score'] ?? 0,
            'recommendation'      => $data['recommendation'] ?? null,
            'shortlist'           => false,
            'resume_path'         => $resumePath,
        ]);
    }

    public function shortlist(int $id): void
    {
        ResumeAnalysis::where('id', $id)->update(['shortlist' => true]);
    }
}
