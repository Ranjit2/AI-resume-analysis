<?php

namespace App\Livewire;

use App\Models\ResumeAnalysis As Candidate;
use Livewire\Component;

class CandidatesList extends Component
{
    public $candidates = [];
    public $selectedCandidate = null;

    public function mount()
    {
        $this->loadCandidates();
    }

    public function loadCandidates()
    {
        $this->candidates = Candidate::where('shortlist', 1)
            ->with('job')
            ->latest()
            ->get();
    }

    public function openModal($id)
    {
        $this->selectedCandidate = Candidate::with('job')->find($id);
    }

    public function closeModal()
    {
        $this->selectedCandidate = null;
    }

    public function removeFromShortlist($candidateId)
    {
        Candidate::where('id', $candidateId)->update(['shortlist' => 0]);
        $this->selectedCandidate = null;
        $this->loadCandidates();
    }

    public function render()
    {
        return view('livewire.candidates-list');
    }
}