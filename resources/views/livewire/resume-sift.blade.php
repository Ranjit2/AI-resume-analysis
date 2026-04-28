<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">

        {{-- HEADER --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">HireLens</h1>
            <p class="text-gray-600">AI Resume Screening System</p>
        </div>

        {{-- FORM --}}
        <form wire:submit.prevent="analyze" class="bg-white p-8 rounded-xl border space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- JOB SELECT -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Job</label>
                    <select wire:model.live="jobId"
                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Job --</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}">{{ $job->title }}</option>
                        @endforeach
                    </select>
                    @error('jobId') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- RESUME UPLOAD -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Resume (PDF)</label>
                    <input type="file" wire:model="resumeFile" accept=".pdf"
                           class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div wire:loading wire:target="resumeFile" class="text-sm text-blue-600 mt-1">Uploading...</div>
                    @error('resumeFile') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- JOB DESCRIPTION (Auto-filled) -->
            @if($jobId)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Job Description</label>
                <div class="w-full p-3 border rounded-lg bg-gray-100 prose prose-sm max-w-none min-h-[8rem]">
                    {!! $jobDescription !!}
                </div>
            </div>
            @endif

            <!-- ANALYZE BUTTON -->
            <button type="submit" wire:loading.attr="disabled" wire:target="analyze"
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white py-4 rounded-lg font-semibold text-lg transition-colors">
                <span wire:loading.remove wire:target="analyze">Analyze Candidate</span>
                <span wire:loading wire:target="analyze">
                    <svg class="animate-spin inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Analyzing...
                </span>
            </button>
        </form>

        {{-- ERROR MESSAGE --}}
        @if($error)
            <div class="mt-6 p-4 bg-red-50 text-red-700 border border-red-200 rounded-lg flex items-center gap-3">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $error }}
            </div>
        @endif


       @if($result)
    
    <div class="mt-8 space-y-6">

        {{-- CANDIDATE HEADER --}}
        <div class="bg-white p-6 rounded-xl border shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $result['candidate_name'] ?? 'Candidate' }}</h2>
                        <p class="text-gray-500">{{ $result['most_recent_role'] ?? 'Role' }}</p>
                    </div>
                </div>
                @php
                    $rawScore = $result['confidence_score'] ?? 0;
                    $pct = $rawScore > 1 ? $rawScore : $rawScore * 100;
                @endphp
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-600 bg-green-100 border border-green-200 px-4 py-2 rounded-xl">
                        {{ match(true) { $pct >= 85 => 'A+', $pct >= 70 => 'A', default => 'B' } }}
                    </div>
                </div>
            </div>
        </div>

        {{-- AI SUMMARY --}}
        @if(!empty($result['summary']))
        <div class="rounded-xl border border-blue-100 bg-blue-50 p-5">
            <p class="text-xs font-semibold text-blue-400 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                AI Summary
            </p>
            <p class="text-sm text-blue-900 leading-relaxed">{{ $result['summary'] }}</p>
        </div>
        @endif

        {{-- BAR CHART - FIXED HEIGHT + HARDCODED JS --}}
        <div class="bg-white p-6 rounded-xl border shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Skill Proficiency</h3>
            <div wire:ignore style="position: relative; width: 100%; height: 280px;">
                <canvas id="skillBarChart" style="width:100%;height:100%;"></canvas>
            </div>
            <div class="flex flex-wrap justify-center gap-4 mt-4 text-xs text-gray-600">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500"></span>Strong (80%+)</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-500"></span>Good (60-79%)</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-500"></span>Basic (40-59%)</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-400"></span>Low (&lt;40%)</span>
            </div>
        </div>

        {{-- RADAR CHART --}}
        <div class="bg-white p-6 rounded-xl border shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Skill Gap Analysis</h3>
            <div wire:ignore style="position: relative; width: 100%; height: 280px;">
                <canvas id="skillRadarChart" style="width:100%;height:100%;"></canvas>
            </div>
            <div class="flex justify-center gap-4 mt-3 text-xs text-gray-600">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-500"></span>Candidate</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-400"></span>Required</span>
            </div>
        </div>

        {{-- WORK EXPERIENCE TIMELINE --}}
        @if(!empty($result['work_experience']))
        <div class="bg-white p-6 rounded-xl border shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Work Experience Timeline</h3>
            <div wire:ignore style="position: relative; width: 100%; height: 280px;">
                <canvas id="experienceChart" style="width:100%;height:100%;"></canvas>
            </div>
        </div>
        @endif

        {{-- SKILLS TAGS (using your real AI data) --}}
        <div class="bg-white p-6 rounded-xl border shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Skills Analysis</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="font-medium text-green-800 mb-2">✅ Matching</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($result['skills_matched'] ?? [] as $skill)
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="font-medium text-gray-800 mb-2">⚠️ Missing</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($result['skills_missing'] ?? [] as $skill)
                            <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

                {{-- ROW 5: INTERVIEW QUESTIONS --}}
                <div class="bg-white p-6 rounded-xl border shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Suggested Interview Questions
                        <span class="ml-auto bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full">{{ count($result['interview_questions'] ?? []) }} Questions</span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($result['interview_questions'] ?? [] as $index => $question)
                            <div class="flex gap-3 p-4 bg-orange-50 rounded-lg border border-orange-100">
                                <span class="flex-shrink-0 w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center text-sm font-bold">{{ $index + 1 }}</span>
                                <span class="text-gray-700">{{ $question }}</span>
                            </div>
                        @empty
                            <div class="col-span-2 text-center text-gray-400 py-8">No interview questions suggested</div>
                        @endforelse
                    </div>
                </div>

                {{-- ROW 6: FINAL RECOMMENDATION --}}
                @if(isset($result['recommendation']))
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 rounded-xl shadow-lg">
                        <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Final Recommendation
                        </h3>
                        <p class="text-blue-100 text-base leading-relaxed">{{ $result['recommendation'] }}</p>
                    </div>
                @endif

    </div>

  
@endif
</div>
</div>

    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
<script>
document.addEventListener('livewire:init', function () {
    Livewire.on('chartDataReady', function (event) {
        const skillData = event.skills;
        const experience = event.experience || [];

        const barLabels = Object.keys(skillData);
        const barValues = Object.values(skillData);
        const barColors = barValues.map(v =>
            v >= 80 ? '#10b981' :
            v >= 60 ? '#3b82f6' :
            v >= 40 ? '#fbbf24' : '#9ca3af'
        );
        const requiredValues = barLabels.map(() => 90);

        setTimeout(function () {
            renderBarChart(barLabels, barValues, barColors);
            renderRadarChart(barLabels, barValues, requiredValues);
            if (experience.length > 0) renderExperienceChart(experience);
        }, 100);
    });
});

function renderBarChart(labels, values, colors) {
    const canvas = document.getElementById('skillBarChart');
    if (!canvas) { console.error('❌ skillBarChart canvas not found'); return; }

    if (window.skillBarChartInstance) window.skillBarChartInstance.destroy();

    window.skillBarChartInstance = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Proficiency',
                data: values,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1,
                borderRadius: 4,
                barThickness: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { callback: v => v + '%', font: { size: 10 } }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: '500' } }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const v = ctx.raw;
                            const lvl = v >= 80 ? 'Strong' : v >= 60 ? 'Good' : v >= 40 ? 'Basic' : 'Low';
                            return `${lvl} — ${v}%`;
                        }
                    }
                }
            },
            animation: { duration: 800 }
        }
    });
    console.log('✅ Bar chart rendered');
}

function renderRadarChart(labels, values, required) {
    const canvas = document.getElementById('skillRadarChart');
    if (!canvas) { console.error('❌ skillRadarChart canvas not found'); return; }

    if (window.skillRadarChartInstance) window.skillRadarChartInstance.destroy();

    window.skillRadarChartInstance = new Chart(canvas, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Candidate',
                    data: values,
                    backgroundColor: 'rgba(59,130,246,0.25)',
                    borderColor: 'rgb(59,130,246)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgb(59,130,246)',
                    pointRadius: 4
                },
                {
                    label: 'Required',
                    data: required,
                    backgroundColor: 'rgba(248,113,113,0.15)',
                    borderColor: 'rgb(248,113,113)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointBackgroundColor: 'rgb(248,113,113)',
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { display: false, stepSize: 25 },
                    grid: { color: 'rgba(0,0,0,0.08)' },
                    angleLines: { color: 'rgba(0,0,0,0.1)' },
                    pointLabels: { font: { size: 10, weight: '600' }, color: '#374151', padding: 8 }
                }
            },
            plugins: {
                legend: { display: true, position: 'bottom', labels: { font: { size: 11 }, padding: 15 } }
            },
            animation: { duration: 1000 }
        }
    });
    console.log('✅ Radar chart rendered');
}

function renderExperienceChart(experience) {
    const canvas = document.getElementById('experienceChart');
    if (!canvas) return;

    if (window.experienceChartInstance) window.experienceChartInstance.destroy();

    const parseDate = (str) => {
        if (!str || str.toLowerCase() === 'present') return new Date();
        const [y, m] = str.split('-');
        return new Date(parseInt(y), parseInt(m || 1) - 1, 1);
    };

    const colors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4','#ec4899'];
    const labels = experience.map(e => e.company);
    const datasets = experience.map((e, i) => ({
        label: e.role,
        data: [{ x: [parseDate(e.start).getTime(), parseDate(e.end).getTime()], y: e.company }],
        backgroundColor: colors[i % colors.length] + '99',
        borderColor: colors[i % colors.length],
        borderWidth: 2,
        borderRadius: 4,
        barThickness: 22,
    }));

    window.experienceChartInstance = new Chart(canvas, {
        type: 'bar',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: ctx => ctx[0].dataset.label + ' @ ' + ctx[0].label,
                        label: ctx => {
                            const [s, e] = ctx.raw.x;
                            const fmt = t => new Date(t).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                            return fmt(s) + ' → ' + fmt(e);
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'linear',
                    min: Math.min(...experience.map(e => parseDate(e.start).getTime())),
                    ticks: {
                        callback: v => new Date(v).getFullYear(),
                        font: { size: 10 },
                        maxTicksLimit: 8,
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: '500' } }
                }
            },
            animation: { duration: 800 }
        }
    });
}
</script>
