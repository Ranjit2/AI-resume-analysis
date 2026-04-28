<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeAnalysis extends Model
{
    use HasFactory;
    protected $fillable = [
        'job_id',
        'candidate_name',
        'email',
        'summary',
        'core_skills',
        'missing_skills',
        'interview_questions',
        'confidence_score',
        'grade',
        'skill_proficiency',
        'work_experience',
        'recommendation',
        'resume_path',
        'shortlist'
    ];

    protected $casts = [
        'core_skills'        => 'array',
        'missing_skills'     => 'array',
        'interview_questions' => 'array',
        'skill_proficiency'  => 'array',
        'work_experience'    => 'array',
        'shortlist'          => 'boolean',
    ];

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
