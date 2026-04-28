<?php

namespace App\Livewire;

use App\Contracts\JobRepositoryInterface;
use App\Contracts\ResumeAnalysisRepositoryInterface;
use App\Services\ResumeAnalysisService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class ResumeSift extends Component
{
    use WithFileUploads;

    public $jobId;
    public string $jobDescription = '';
    public $resumeFile;
    public $result = null;
    public bool $loading = false;
    public string $error = '';
    public bool $shortlisted = false;
    public $candidateId = null;

    protected $rules = [
        'jobId'      => 'required|exists:jobs,id',
        'resumeFile' => 'required|file|mimes:pdf|max:3072',
    ];

    protected JobRepositoryInterface $jobs;
    protected ResumeAnalysisRepositoryInterface $analyses;
    protected ResumeAnalysisService $service;

    public function boot(
        JobRepositoryInterface $jobs,
        ResumeAnalysisRepositoryInterface $analyses,
        ResumeAnalysisService $service,
    ): void {
        $this->jobs     = $jobs;
        $this->analyses = $analyses;
        $this->service  = $service;
    }

    public function updatedJobId(int $value): void
    {
        $this->jobDescription = $this->jobs->find($value)?->description ?? '';
    }

    public function analyze(): void
    {
        $this->validate();

        $this->loading = true;
        $this->error   = '';
        $this->result  = null;

        try {
            $job      = $this->jobs->findOrFail($this->jobId);
            $data     = $this->service->analyze($job->description, $this->resumeFile->getRealPath());
            $analysis = $this->analyses->create($job->id, $data, $this->resumeFile->store('resumes', 'public'));

            $this->result      = $data;
            $this->candidateId = $analysis->id;

            $this->dispatch('chartDataReady',
                skills:     $data['skill_proficiency'] ?? [],
                experience: $data['work_experience'] ?? [],
            );
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Log::error('ResumeSift analyze error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        } finally {
            $this->loading = false;
        }
    }

    public function shortlist(): void
    {
        if ($this->candidateId) {
            $this->analyses->shortlist($this->candidateId);
            $this->shortlisted = true;
            $this->dispatch('notify', message: 'Candidate shortlisted!', type: 'success');
        }
    }

    public function render()
    {
        return view('livewire.resume-sift', [
            'jobs' => $this->jobs->latest(),
        ]);
    }
}
