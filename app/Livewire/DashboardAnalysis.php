<?php

namespace App\Livewire;

use App\Contracts\JobRepositoryInterface;
use App\Contracts\ResumeAnalysisRepositoryInterface;
use App\Services\ResumeAnalysisService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class DashboardAnalysis extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public string $description = '';
    public $resumeFile = null;
    public $result = null;
    public string $error = '';
    public $candidateId = null;
    public bool $shortlisted = false;

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

    public function nextStep(): void
    {
        $this->validate(
            ['description' => 'required|min:20'],
            [
                'description.required' => 'Please enter a job description before continuing.',
                'description.min'      => 'The description must be at least 20 characters.',
            ]
        );

        $this->step = 2;
    }

    public function prevStep(): void
    {
        $this->step = 1;
        $this->resetErrorBag();
    }

    public function analyze(): void
    {
        $this->validate(
            ['resumeFile' => 'required|file|mimes:pdf|max:3072'],
            [
                'resumeFile.required' => 'Please upload a PDF resume before analyzing.',
                'resumeFile.mimes'    => 'Only PDF files are accepted.',
                'resumeFile.max'      => 'Resume must be under 3MB.',
            ]
        );

        $this->error = '';

        try {
            $job      = $this->jobs->create($this->description);
            $data     = $this->service->analyze($this->description, $this->resumeFile->getRealPath());
            $analysis = $this->analyses->create($job->id, $data, $this->resumeFile->store('resumes', 'public'));

            $this->result      = $data;
            $this->candidateId = $analysis->id;
            $this->step        = 3;

            $this->dispatch('dashboardChartsReady',
                skills:     $data['skill_proficiency'] ?? [],
                experience: $data['work_experience'] ?? [],
            );
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->step  = 2;
            Log::error('DashboardAnalysis error: ' . $e->getMessage());
        }
    }

    public function shortlist(): void
    {
        if ($this->candidateId) {
            $this->analyses->shortlist($this->candidateId);
            $this->shortlisted = true;
        }
    }

    public function startOver(): void
    {
        $this->reset(['description', 'resumeFile', 'result', 'error', 'candidateId', 'shortlisted']);
        $this->step = 1;
        $this->dispatch('resetDashboardQuill');
    }

    public function render()
    {
        return view('livewire.dashboard-analysis');
    }
}
