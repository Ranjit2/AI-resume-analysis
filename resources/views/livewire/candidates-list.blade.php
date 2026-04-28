<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Shortlisted Candidates</h1>
            <p class="text-gray-600 mt-2">{{ count($candidates) }} candidate(s) shortlisted</p>
        </div>

        <!-- Candidates Grid -->
        @if(count($candidates) > 0)
            <div class="grid gap-6 md:grid-cols-2">
                @foreach($candidates as $candidate)
                    @php
                        $raw = $candidate->confidence_score ?? 0;
                        $pct = $raw > 1 ? $raw : $raw * 100;
                        $grade = match(true) {
                            $pct >= 85 => 'A+',
                            $pct >= 70 => 'A',
                            $pct >= 55 => 'B+',
                            $pct >= 40 => 'B',
                            default    => 'C',
                        };
                    @endphp
                    <div wire:click="openModal({{ $candidate->id }})"
                         class="bg-white rounded-xl border shadow-sm hover:shadow-md hover:border-blue-200 transition-all cursor-pointer group">

                        <!-- Card Header -->
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-violet-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 truncate group-hover:text-violet-700 transition-colors">
                                        {{ $candidate->candidate_name ?? 'Unknown' }}
                                    </h3>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $candidate->most_recent_role ?? $candidate->email ?? '—' }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-3xl font-bold text-green-600 bg-green-100 border border-green-200 px-4 py-2 rounded-xl">
                                    {{ $grade }}
                                </div>
                            </div>

                            @if($candidate->summary)
                                <p class="mt-4 text-sm text-gray-600 line-clamp-2">{{ $candidate->summary }}</p>
                            @endif

                            @php $skills = is_array($candidate->core_skills) ? $candidate->core_skills : (json_decode($candidate->core_skills, true) ?? []); @endphp
                            @if(count($skills) > 0)
                                <div class="mt-4 flex flex-wrap gap-1">
                                    @foreach(array_slice($skills, 0, 4) as $skill)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">{{ $skill }}</span>
                                    @endforeach
                                    @if(count($skills) > 4)
                                        <span class="px-2 py-1 text-gray-400 text-xs">+{{ count($skills) - 4 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer -->
                        <div class="px-6 py-3 bg-gray-50 border-t rounded-b-xl flex items-center justify-between">
                            <span class="text-xs text-gray-400">Added {{ $candidate->created_at->diffForHumans() }}</span>
                            <span class="text-xs text-blue-500 font-medium group-hover:underline">View details →</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl border p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No shortlisted candidates</h3>
                <p class="text-gray-500 mb-6">Start analyzing resumes to shortlist candidates</p>
                <a href="{{ route('resume-sift') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Analyze Resume
                </a>
            </div>
        @endif

    </div>

    {{-- CANDIDATE DETAIL MODAL --}}
    @if($selectedCandidate)
        @php
            $c = $selectedCandidate;
            $rawS = $c->confidence_score ?? 0;
            $pctS = $rawS > 1 ? $rawS : $rawS * 100;
            $gradeS = match(true) {
                $pctS >= 85 => 'A+',
                $pctS >= 70 => 'A',
                $pctS >= 55 => 'B+',
                $pctS >= 40 => 'B',
                default     => 'C',
            };
            $matched     = is_array($c->core_skills)         ? $c->core_skills         : (json_decode($c->core_skills, true) ?? []);
            $missing     = is_array($c->missing_skills)      ? $c->missing_skills      : (json_decode($c->missing_skills, true) ?? []);
            $questions   = is_array($c->interview_questions) ? $c->interview_questions : (json_decode($c->interview_questions, true) ?? []);
            $proficiency = is_array($c->skill_proficiency)   ? $c->skill_proficiency   : (json_decode($c->skill_proficiency, true) ?? []);
            $experience  = is_array($c->work_experience)     ? $c->work_experience     : (json_decode($c->work_experience, true) ?? []);
        @endphp

        <div x-data="{
                init() {
                    document.body.style.overflow = 'hidden';
                    const labels = @js(array_keys($proficiency));
                    const values = @js(array_values($proficiency));
                    const required = labels.map(() => 90);
                    const colors = values.map(v => v >= 80 ? '#10b981' : v >= 60 ? '#3b82f6' : v >= 40 ? '#fbbf24' : '#9ca3af');
                    const experience = @js($experience);
                    this.$nextTick(() => {
                        const bar = document.getElementById('modalBarChart');
                        const radar = document.getElementById('modalRadarChart');
                        const expCanvas = document.getElementById('modalExperienceChart');
                        if (bar) {
                            if (window.modalBarInstance) window.modalBarInstance.destroy();
                            window.modalBarInstance = new Chart(bar, {
                                type: 'bar',
                                data: { labels, datasets: [{ label: 'Proficiency', data: values, backgroundColor: colors, borderRadius: 4, barThickness: 18 }] },
                                options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.05)' } }, y: { grid: { display: false }, ticks: { font: { size: 11 } } } }, animation: { duration: 600 } }
                            });
                        }
                        if (radar) {
                            if (window.modalRadarInstance) window.modalRadarInstance.destroy();
                            window.modalRadarInstance = new Chart(radar, {
                                type: 'radar',
                                data: { labels, datasets: [
                                    { label: 'Candidate', data: values, backgroundColor: 'rgba(59,130,246,0.25)', borderColor: 'rgb(59,130,246)', borderWidth: 2, pointBackgroundColor: 'rgb(59,130,246)', pointRadius: 4 },
                                    { label: 'Required', data: required, backgroundColor: 'rgba(248,113,113,0.15)', borderColor: 'rgb(248,113,113)', borderWidth: 2, borderDash: [5,5], pointBackgroundColor: 'rgb(248,113,113)', pointRadius: 4 }
                                ]},
                                options: { responsive: true, maintainAspectRatio: false, scales: { r: { beginAtZero: true, max: 100, ticks: { display: false }, grid: { color: 'rgba(0,0,0,0.08)' }, pointLabels: { font: { size: 10, weight: '600' }, color: '#374151' } } }, plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 11 }, padding: 12 } } }, animation: { duration: 800 } }
                            });
                        }
                        if (expCanvas && experience.length > 0) {
                            if (window.modalExpInstance) window.modalExpInstance.destroy();
                            const parseDate = s => (!s || s.toLowerCase() === 'present') ? new Date() : new Date(parseInt(s.split('-')[0]), parseInt(s.split('-')[1] || 1) - 1, 1);
                            const expColors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4','#ec4899'];
                            window.modalExpInstance = new Chart(expCanvas, {
                                type: 'bar',
                                data: {
                                    labels: experience.map(e => e.company),
                                    datasets: experience.map((e, i) => ({
                                        label: e.role,
                                        data: [{ x: [parseDate(e.start).getTime(), parseDate(e.end).getTime()], y: e.company }],
                                        backgroundColor: expColors[i % expColors.length] + '99',
                                        borderColor: expColors[i % expColors.length],
                                        borderWidth: 2, borderRadius: 4, barThickness: 22
                                    }))
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: { callbacks: {
                                            title: ctx => ctx[0].dataset.label + ' @ ' + ctx[0].label,
                                            label: ctx => { const [s,e] = ctx.raw.x; const f = t => new Date(t).toLocaleDateString('en-US',{month:'short',year:'numeric'}); return f(s)+' → '+f(e); }
                                        }}
                                    },
                                    scales: {
                                        x: { type: 'linear', min: Math.min(...experience.map(e => parseDate(e.start).getTime())), ticks: { callback: v => new Date(v).getFullYear(), font: { size: 10 }, maxTicksLimit: 8 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                                        y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                                    },
                                    animation: { duration: 800 }
                                }
                            });
                        }
                    });
                },
                destroy() { document.body.style.overflow = ''; }
             }"
             class="fixed inset-0 z-50 flex items-center justify-center p-4">

            {{-- Backdrop --}}
            <div wire:click="closeModal" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

            {{-- Panel --}}
            <div class="relative w-full max-w-5xl max-h-[92vh] overflow-y-auto bg-white rounded-2xl shadow-2xl">

                {{-- Modal Header --}}
                <div class="sticky top-0 z-10 bg-white border-b px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">{{ $c->candidate_name ?? 'Candidate' }}</h2>
                            <p class="text-sm text-gray-500">{{ $c->most_recent_role ?? '' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-3xl font-bold text-green-600 bg-green-100 border border-green-200 px-4 py-1.5 rounded-xl">
                            {{ $gradeS }}
                        </div>
                        <button wire:click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-6">

                    {{-- Meta row --}}
                    <div class="flex flex-wrap gap-3">
                        @if($c->job)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $c->job->title }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $c->created_at->format('M d, Y') }}
                        </span>
                        @if($c->resume_path)
                            <a href="{{ Storage::url($c->resume_path) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                View Resume PDF
                            </a>
                        @endif
                    </div>

                    {{-- AI Summary + Resume Link --}}
                    @if($c->summary)
                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <p class="text-xs font-semibold text-blue-400 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                AI Summary
                            </p>
                            <p class="text-sm text-blue-900 leading-relaxed">{{ $c->summary }}</p>
                            @if($c->resume_path)
                                <a href="{{ Storage::url($c->resume_path) }}" target="_blank"
                                   class="inline-flex items-center gap-2 mt-3 text-sm font-medium text-blue-600 hover:text-blue-800 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    View Full Resume PDF →
                                </a>
                            @endif
                        </div>
                    @endif

                    {{-- AI Recommendation --}}
                    @if($c->recommendation)
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-5">
                            <p class="text-xs font-semibold text-blue-200 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                AI Recommendation
                            </p>
                            <p class="text-sm text-white leading-relaxed">{{ $c->recommendation }}</p>
                        </div>
                    @endif

                    {{-- Skill Proficiency Bar Chart --}}
                    @if(count($proficiency) > 0)
                        <div class="bg-gray-50 rounded-xl p-4 border">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Skill Proficiency</p>
                            <div wire:ignore style="position:relative;width:100%;height:280px;">
                                <canvas id="modalBarChart" style="width:100%;height:100%;"></canvas>
                            </div>
                            <div class="flex flex-wrap justify-center gap-3 mt-3 text-xs text-gray-500">
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span>Strong (80%+)</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-500 inline-block"></span>Good (60-79%)</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-400 inline-block"></span>Basic (40-59%)</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-400 inline-block"></span>Low (&lt;40%)</span>
                            </div>
                        </div>

                        {{-- Radar Chart --}}
                        <div class="bg-gray-50 rounded-xl p-4 border">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Skill Gap Analysis</p>
                            <div wire:ignore style="position:relative;width:100%;height:260px;">
                                <canvas id="modalRadarChart" style="width:100%;height:100%;"></canvas>
                            </div>
                        </div>
                    @endif

                    {{-- Work Experience Timeline --}}
                    @if(count($experience) > 0)
                        <div class="bg-gray-50 rounded-xl p-4 border">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Work Experience Timeline</p>
                            <div wire:ignore style="position:relative;width:100%;height:260px;">
                                <canvas id="modalExperienceChart" style="width:100%;height:100%;"></canvas>
                            </div>
                        </div>
                    @endif

                    {{-- Skills matched / missing --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                            <p class="text-xs font-semibold text-green-700 uppercase tracking-wide mb-3">Matching Skills</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse($matched as $skill)
                                    <span class="px-2.5 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">{{ $skill }}</span>
                                @empty
                                    <span class="text-xs text-gray-400">None listed</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="bg-red-50 rounded-xl p-4 border border-red-100">
                            <p class="text-xs font-semibold text-red-700 uppercase tracking-wide mb-3">Missing Skills</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse($missing as $skill)
                                    <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">{{ $skill }}</span>
                                @empty
                                    <span class="text-xs text-gray-400">None</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Interview Questions --}}
                    @if(count($questions) > 0)
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
                                Suggested Interview Questions
                                <span class="ml-2 bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full normal-case font-medium">{{ count($questions) }}</span>
                            </p>
                            <div class="space-y-2">
                                @foreach($questions as $i => $q)
                                    <div class="flex gap-3 p-3 bg-orange-50 rounded-lg border border-orange-100">
                                        <span class="flex-shrink-0 w-6 h-6 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $i + 1 }}</span>
                                        <span class="text-sm text-gray-700">{{ $q }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Footer Actions --}}
                    <div class="flex items-center justify-between pt-2 border-t">
                        <button wire:click="removeFromShortlist({{ $c->id }})"
                                wire:confirm="Remove this candidate from shortlist?"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Remove from Shortlist
                        </button>
                        <button wire:click="closeModal" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition font-medium">
                            Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
