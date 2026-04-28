<div class="min-h-screen bg-gradient-to-br from-slate-50 via-violet-50/40 to-purple-50 py-10 px-4">
    <div class="max-w-4xl mx-auto">

        {{-- PAGE HEADER --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-violet-600 to-purple-600 rounded-2xl mb-4 shadow-lg shadow-violet-200">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 tracking-tight">HireLens</h1>
            <p class="text-gray-500 mt-1">AI-powered resume screening in seconds</p>
        </div>

        {{-- STEP PROGRESS --}}
        <div class="flex items-center justify-center mb-10">
            @foreach([1 => 'Job Description', 2 => 'Upload Resume', 3 => 'Analysis'] as $num => $label)
                <div class="flex items-center">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300
                            {{ $step > $num ? 'bg-green-500 text-white shadow-md shadow-green-200' : ($step === $num ? 'bg-violet-600 text-white shadow-md shadow-violet-200' : 'bg-white text-gray-400 border-2 border-gray-200') }}">
                            @if($step > $num)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="text-xs mt-1.5 font-medium {{ $step === $num ? 'text-violet-600' : 'text-gray-400' }}">{{ $label }}</span>
                    </div>
                    @if($num < 3)
                        <div class="w-20 h-0.5 mx-2 mb-4 transition-all duration-300 {{ $step > $num ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ──────────────── STEP 1: JOB DESCRIPTION ──────────────── --}}
        @if($step === 1)
            {{-- Skeleton while nextStep() loads --}}
            <div wire:loading wire:target="nextStep" class="animate-pulse space-y-4">
                <div class="bg-white rounded-2xl border shadow-sm p-8">
                    <div class="h-5 bg-gray-200 rounded w-32 mb-6"></div>
                    <div class="h-56 bg-gray-100 rounded-xl mb-6"></div>
                    <div class="h-12 bg-gray-200 rounded-xl w-full"></div>
                </div>
            </div>

            <div wire:loading.remove wire:target="nextStep">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-violet-600 to-purple-600 px-8 py-5">
                        <h2 class="text-xl font-semibold text-white">Define the Role</h2>
                        <p class="text-violet-100 text-sm mt-0.5">Paste or type the job requirements below</p>
                    </div>
                    <div class="p-8">
                        <input type="hidden" wire:model="description" id="dashboardDescInput">
                        <div wire:ignore id="dashboardQuillEditor"
                             class="bg-white border border-gray-200 rounded-xl"
                             style="height: 260px;"></div>

                        @error('description')
                            <div class="mt-3 flex items-center gap-2 text-red-600 text-sm">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </div>
                        @enderror

                        <button type="button"
                            x-on:click="
                                if (window.dashboardQuill) {
                                    @this.set('description', window.dashboardQuill.root.innerHTML, false);
                                }
                                $nextTick(() => @this.call('nextStep'))
                            "
                            class="mt-6 w-full flex items-center justify-center gap-2 bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 text-white py-3.5 rounded-xl font-semibold text-base transition-all shadow-md shadow-violet-200 hover:shadow-lg hover:shadow-violet-300">
                            Next: Upload Resume
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ──────────────── STEP 2: UPLOAD RESUME ──────────────── --}}
        @if($step === 2)
            {{-- Skeleton while analyze() runs (AI processing) --}}
            <div wire:loading wire:target="analyze" class="animate-pulse space-y-5">
                <div class="bg-white rounded-2xl border shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-gray-200 rounded-full"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-6 bg-gray-200 rounded w-48"></div>
                            <div class="h-4 bg-gray-200 rounded w-32"></div>
                        </div>
                        <div class="w-16 h-16 bg-gray-200 rounded-2xl"></div>
                    </div>
                    <div class="h-4 bg-violet-100 rounded w-20 mb-3"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-violet-100 rounded w-full"></div>
                        <div class="h-4 bg-violet-100 rounded w-5/6"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <div class="bg-white rounded-2xl border shadow-sm p-5">
                        <div class="h-4 bg-gray-200 rounded w-32 mb-4"></div>
                        <div class="h-52 bg-gray-100 rounded-xl"></div>
                    </div>
                    <div class="bg-white rounded-2xl border shadow-sm p-5">
                        <div class="h-4 bg-gray-200 rounded w-32 mb-4"></div>
                        <div class="h-52 bg-gray-100 rounded-xl"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <div class="bg-green-50 rounded-2xl border border-green-100 p-5">
                        <div class="h-3 bg-green-200 rounded w-24 mb-3"></div>
                        <div class="flex flex-wrap gap-2">
                            @for($i=0;$i<5;$i++)<div class="h-7 w-20 bg-green-200 rounded-full"></div>@endfor
                        </div>
                    </div>
                    <div class="bg-red-50 rounded-2xl border border-red-100 p-5">
                        <div class="h-3 bg-red-200 rounded w-24 mb-3"></div>
                        <div class="flex flex-wrap gap-2">
                            @for($i=0;$i<4;$i++)<div class="h-7 w-20 bg-red-200 rounded-full"></div>@endfor
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border shadow-sm p-5">
                    <div class="h-4 bg-gray-200 rounded w-48 mb-4"></div>
                    <div class="space-y-2">
                        @for($i=0;$i<3;$i++)
                        <div class="h-12 bg-orange-50 rounded-xl border border-orange-100"></div>
                        @endfor
                    </div>
                </div>
                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl p-5 opacity-40">
                    <div class="h-3 bg-white/40 rounded w-32 mb-3"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-white/40 rounded w-full"></div>
                        <div class="h-4 bg-white/40 rounded w-4/5"></div>
                    </div>
                </div>
            </div>

            <div wire:loading.remove wire:target="analyze">
                @if($error)
                    <div class="mb-5 flex items-center gap-3 p-4 bg-red-50 text-red-700 border border-red-200 rounded-xl">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $error }}
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-violet-600 to-purple-600 px-8 py-5">
                        <h2 class="text-xl font-semibold text-white">Upload Resume</h2>
                        <p class="text-violet-100 text-sm mt-0.5">Upload a PDF resume to analyse against the job description</p>
                    </div>
                    <div class="p-8">
                        <div x-data="{ fileName: null, dragging: false }"
                             @dragover.prevent="dragging = true"
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="
                                 dragging = false;
                                 const file = $event.dataTransfer.files[0];
                                 if (file) {
                                     fileName = file.name;
                                     const dt = new DataTransfer(); dt.items.add(file);
                                     $refs.resumeInput.files = dt.files;
                                     $refs.resumeInput.dispatchEvent(new Event('change'));
                                 }
                             "
                             :class="dragging ? 'border-violet-500 bg-violet-50' : 'border-gray-200 hover:border-violet-300 hover:bg-violet-50/50'"
                             class="relative border-2 border-dashed rounded-2xl p-10 text-center cursor-pointer transition-all duration-200"
                             @click="$refs.resumeInput.click()">

                            <input type="file" wire:model="resumeFile" accept=".pdf"
                                   x-ref="resumeInput"
                                   @change="fileName = $event.target.files[0]?.name ?? null"
                                   class="hidden">

                            <template x-if="!fileName">
                                <div>
                                    <div class="w-16 h-16 bg-violet-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                    </div>
                                    <p class="text-base font-medium text-gray-700"><span class="text-violet-600">Click to upload</span> or drag & drop</p>
                                    <p class="text-sm text-gray-400 mt-1">PDF only · Max 3MB</p>
                                </div>
                            </template>

                            <template x-if="fileName">
                                <div class="flex items-center justify-center gap-4">
                                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <p class="font-medium text-gray-800 truncate max-w-xs" x-text="fileName"></p>
                                        <p class="text-sm text-green-600 font-medium">Ready to analyse</p>
                                    </div>
                                    <button type="button" @click.stop="fileName = null; $refs.resumeInput.value = ''"
                                            class="text-gray-400 hover:text-red-500 transition-colors p-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div wire:loading wire:target="resumeFile" class="flex items-center gap-2 text-sm text-violet-600 mt-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Uploading file...
                        </div>

                        @error('resumeFile')
                            <div class="mt-3 flex items-center gap-2 text-red-600 text-sm">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="flex gap-3 mt-6">
                            <button type="button" wire:click="prevStep"
                                    class="flex items-center gap-2 px-5 py-3 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                                Back
                            </button>
                            <button type="button" wire:click="analyze"
                                    wire:loading.attr="disabled" wire:target="analyze"
                                    class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 disabled:opacity-60 text-white py-3 rounded-xl font-semibold transition-all shadow-md shadow-violet-200">
                                <span wire:loading.remove wire:target="analyze" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                    Analyse Candidate
                                </span>
                                <span wire:loading wire:target="analyze" class="flex items-center gap-2">
                                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    Analysing...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ──────────────── STEP 3: RESULTS ──────────────── --}}
        @if($step === 3 && $result)
            @php
                $rawScore = $result['confidence_score'] ?? 0;
                $pct      = $rawScore > 1 ? $rawScore : $rawScore * 100;
                $grade    = match(true) { $pct >= 85 => 'A+', $pct >= 70 => 'A', $pct >= 55 => 'B+', $pct >= 40 => 'B', default => 'C' };
                $gradeColor = match($grade) { 'A+', 'A' => 'text-green-600 bg-green-100 border-green-200', 'B+', 'B' => 'text-yellow-600 bg-yellow-100 border-yellow-200', default => 'text-red-600 bg-red-100 border-red-200' };
            @endphp

            <div class="space-y-5">

                {{-- CANDIDATE HEADER --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-900 to-gray-700 px-8 py-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-white">{{ $result['candidate_name'] ?? 'Candidate' }}</h2>
                                    <p class="text-gray-400 text-sm mt-0.5">{{ $result['most_recent_role'] ?? '' }}</p>
                                    @if(!empty($result['email']))
                                        <p class="text-gray-400 text-xs mt-1">{{ $result['email'] }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-5xl font-black border-2 px-5 py-3 rounded-2xl {{ $gradeColor }}">{{ $grade }}</div>
                                <p class="text-gray-400 text-xs mt-2">Match Score</p>
                            </div>
                        </div>
                    </div>

                    {{-- AI Summary inside header card --}}
                    @if(!empty($result['summary']))
                        <div class="px-8 py-5 bg-violet-50 border-t border-violet-100">
                            <p class="text-xs font-semibold text-violet-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                AI Summary
                            </p>
                            <p class="text-sm text-violet-900 leading-relaxed">{{ $result['summary'] }}</p>
                        </div>
                    @endif
                </div>

                {{-- CHARTS: BAR + RADAR side by side --}}
                @if(!empty($result['skill_proficiency']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-4">Skill Proficiency</h3>
                            <div wire:ignore style="position:relative;width:100%;height:240px;">
                                <canvas id="dbBarChart" style="width:100%;height:100%;"></canvas>
                            </div>
                            <div class="flex flex-wrap justify-center gap-3 mt-3 text-xs text-gray-500">
                                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-emerald-500 inline-block"></span>Strong</span>
                                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-blue-500 inline-block"></span>Good</span>
                                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-yellow-400 inline-block"></span>Basic</span>
                                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-gray-300 inline-block"></span>Low</span>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-4">Skill Gap Analysis</h3>
                            <div wire:ignore style="position:relative;width:100%;height:240px;">
                                <canvas id="dbRadarChart" style="width:100%;height:100%;"></canvas>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- WORK EXPERIENCE TIMELINE --}}
                @if(!empty($result['work_experience']))
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Work Experience Timeline</h3>
                        <div wire:ignore style="position:relative;width:100%;height:260px;">
                            <canvas id="dbExpChart" style="width:100%;height:100%;"></canvas>
                        </div>
                    </div>
                @endif

                {{-- SKILLS: MATCHED + MISSING --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="bg-green-50 rounded-2xl border border-green-100 p-5">
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-widest mb-3">Matching Skills</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse($result['skills_matched'] ?? [] as $skill)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">{{ $skill }}</span>
                            @empty
                                <span class="text-xs text-gray-400">None listed</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-red-50 rounded-2xl border border-red-100 p-5">
                        <p class="text-xs font-semibold text-red-500 uppercase tracking-widest mb-3">Missing Skills</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse($result['skills_missing'] ?? [] as $skill)
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">{{ $skill }}</span>
                            @empty
                                <span class="text-xs text-gray-400">None</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- INTERVIEW QUESTIONS --}}
                @if(!empty($result['interview_questions']))
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-700">Suggested Interview Questions</h3>
                            <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-2.5 py-1 rounded-full">{{ count($result['interview_questions']) }}</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($result['interview_questions'] as $i => $q)
                                <div class="flex gap-3 p-3.5 bg-orange-50 rounded-xl border border-orange-100">
                                    <span class="flex-shrink-0 w-7 h-7 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $i + 1 }}</span>
                                    <span class="text-sm text-gray-700 leading-snug">{{ $q }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- RECOMMENDATION --}}
                @if(!empty($result['recommendation']))
                    <div class="bg-gradient-to-r from-violet-600 to-purple-600 rounded-2xl p-6 shadow-lg shadow-violet-200">
                        <p class="text-xs font-semibold text-violet-200 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            AI Recommendation
                        </p>
                        <p class="text-white leading-relaxed">{{ $result['recommendation'] }}</p>
                    </div>
                @endif

                {{-- ACTION BUTTONS --}}
                <div class="flex items-center gap-3 pb-6">
                    @if(!$shortlisted)
                        <button wire:click="shortlist"
                                class="flex items-center gap-2 px-5 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition-colors shadow-md shadow-green-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Shortlist Candidate
                        </button>
                    @else
                        <span class="flex items-center gap-2 px-5 py-3 bg-green-100 text-green-700 rounded-xl font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Shortlisted
                        </span>
                    @endif
                    <button wire:click="startOver"
                            class="flex items-center gap-2 px-5 py-3 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Start Over
                    </button>
                </div>

            </div>
        @endif

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.dashboardQuill) return;

    window.dashboardQuill = new Quill('#dashboardQuillEditor', {
        theme: 'snow',
        placeholder: 'Paste the job description here — requirements, responsibilities, skills needed...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['clean']
            ]
        }
    });

    let syncTimeout;
    window.dashboardQuill.on('text-change', function () {
        clearTimeout(syncTimeout);
        syncTimeout = setTimeout(function () {
            @this.set('description', window.dashboardQuill.root.innerHTML, false);
        }, 400);
    });

    Livewire.on('resetDashboardQuill', function () {
        if (window.dashboardQuill) window.dashboardQuill.setContents([]);
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('dashboardChartsReady', function (event) {
        const skills     = event.skills     || {};
        const experience = event.experience || [];

        const labels = Object.keys(skills);
        const values = Object.values(skills);
        const colors = values.map(v => v >= 80 ? '#10b981' : v >= 60 ? '#3b82f6' : v >= 40 ? '#fbbf24' : '#9ca3af');

        setTimeout(function () {
            const barCanvas   = document.getElementById('dbBarChart');
            const radarCanvas = document.getElementById('dbRadarChart');
            const expCanvas   = document.getElementById('dbExpChart');

            if (barCanvas) {
                if (window.dbBarInstance) window.dbBarInstance.destroy();
                window.dbBarInstance = new Chart(barCanvas, {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'Proficiency', data: values, backgroundColor: colors, borderRadius: 4, barThickness: 18 }] },
                    options: {
                        responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                            y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                        },
                        animation: { duration: 700 }
                    }
                });
            }

            if (radarCanvas) {
                if (window.dbRadarInstance) window.dbRadarInstance.destroy();
                window.dbRadarInstance = new Chart(radarCanvas, {
                    type: 'radar',
                    data: { labels, datasets: [
                        { label: 'Candidate', data: values, backgroundColor: 'rgba(59,130,246,0.25)', borderColor: '#3b82f6', borderWidth: 2, pointBackgroundColor: '#3b82f6', pointRadius: 4 },
                        { label: 'Required',  data: labels.map(() => 90), backgroundColor: 'rgba(248,113,113,0.15)', borderColor: '#f87171', borderWidth: 2, borderDash: [5,5], pointBackgroundColor: '#f87171', pointRadius: 4 }
                    ]},
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        scales: { r: { beginAtZero: true, max: 100, ticks: { display: false }, grid: { color: 'rgba(0,0,0,0.07)' }, pointLabels: { font: { size: 10, weight: '600' }, color: '#374151' } } },
                        plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 11 }, padding: 12 } } },
                        animation: { duration: 900 }
                    }
                });
            }

            if (expCanvas && experience.length > 0) {
                if (window.dbExpInstance) window.dbExpInstance.destroy();
                const parseDate = s => (!s || s.toLowerCase() === 'present') ? new Date() : new Date(parseInt(s.split('-')[0]), parseInt(s.split('-')[1] || 1) - 1, 1);
                const expColors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4'];
                window.dbExpInstance = new Chart(expCanvas, {
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
        }, 150);
    });
});
</script>
