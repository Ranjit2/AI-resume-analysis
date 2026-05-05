# Resume Sift — AI Resume Analyser

Laravel 10 + Livewire 3 app that lets recruiters upload a PDF resume, run it against a job description, and get a structured AI analysis (skills match, proficiency scores, interview questions, hire recommendation).

---

## Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 10, PHP 8.1+ |
| UI | Livewire 3 (no Inertia, no Vue/React) |
| Auth | Laravel Breeze (session-based) |
| CSS | Tailwind CSS via Vite |
| PDF parsing | `smalot/pdfparser` |
| AI | OpenRouter API → `anthropic/claude-3-haiku` |
| Tests | Pest 2 |

---

## Architecture

### Data flow

```
ResumeSift (Livewire)
  └─ ResumeAnalysisService::analyze()
       ├─ extractText()     — smalot/pdfparser, truncated to 6 000 chars
       └─ callAI()          — POST to OpenRouter, returns structured JSON
  └─ ResumeAnalysisRepository::create()  — persists to resume_analyses table
```

### Repository pattern

All DB access goes through interfaces, not Eloquent directly from components.

- `app/Contracts/JobRepositoryInterface.php`
- `app/Contracts/ResumeAnalysisRepositoryInterface.php`
- Concrete implementations live in `app/Repositories/`
- Bound in `app/Providers/AppServiceProvider.php`

Always inject the interface, never the concrete class.

Bindings (registered in `AppServiceProvider::register()`):

```php
JobRepositoryInterface::class          → JobRepository::class
ResumeAnalysisRepositoryInterface::class → ResumeAnalysisRepository::class
```

### Livewire components

| Component | Route | Purpose |
|---|---|---|
| `ResumeSift` | `/resume` | Upload PDF + job selection → trigger analysis |
| `CandidatesList` | `/candidates` | List all analysed candidates, filter/shortlist |
| `DashboardAnalysis` | `/dashboard` | Summary stats |

---

## AI integration

**Config key:** `config('services.openrouter.key')` — set `OPENROUTER_API_KEY` in `.env`

**Model:** `anthropic/claude-3-haiku` via OpenRouter

**Prompt:** defined in `ResumeAnalysisService::buildPrompt()`. Returns a single JSON object — never markdown, never prose.

### AI response shape

```json
{
  "candidate_name": "string",
  "email": "string|null",
  "most_recent_role": "string",
  "summary": "string",
  "skills_matched": ["string"],
  "skills_missing": ["string"],
  "skill_proficiency": { "SkillName": 0-100 },
  "work_experience": [{ "company": "", "role": "", "start": "YYYY-MM", "end": "YYYY-MM|present" }],
  "interview_questions": ["string x6"],
  "recommendation": "string",
  "confidence_score": 0.0-1.0
}
```

If `skills_matched` is missing from the response, `ResumeAnalysisService::analyze()` throws — callers must handle that.

### Skill proficiency scoring bands

| Score | Label |
|---|---|
| 90–100 | Expert |
| 70–89 | Proficient |
| 40–69 | Intermediate |
| 0–39 | Beginner |

---

## Database

### `jobs`
- `id`, `title` (nullable), `description`, timestamps

### `resume_analyses`
- `id`, `job_id` (FK), `candidate_name`, `email`, `summary`, `most_recent_role`
- `core_skills` (JSON array — mapped from `skills_matched`)
- `missing_skills` (JSON array)
- `skill_proficiency` (JSON object)
- `work_experience` (JSON array)
- `interview_questions` (JSON array)
- `confidence_score`, `recommendation`, `resume_path`, `shortlist` (bool), timestamps

JSON columns are cast in `ResumeAnalysis` model — always access as arrays, never raw strings.

---

## Key conventions

- **No direct Eloquent in Livewire** — always go through repository interfaces.
- **PDF text is capped at 6 000 chars** before being sent to the AI (`mb_strimwidth`). Keep this in mind for very long resumes.
- **Resume files** are stored in `storage/app/public/resumes` via `resumeFile->store('resumes', 'public')`. Run `php artisan storage:link` after setup.
- **Livewire events**: `ResumeSift` dispatches `chartDataReady` (skills + experience) and `notify` (shortlist toast) — listeners are in the Blade view.
- **Validation**: PDF only, max 3 MB (`mimes:pdf|max:3072`).
- **Temperature**: AI calls use `temperature: 0.1` — keep it low for consistent structured output.

---

## Local setup

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run dev          # or npm run build
php artisan serve
```

Required `.env` values:

```
OPENROUTER_API_KEY=sk-or-...
DB_DATABASE=...
```

---

## Running tests

```bash
./vendor/bin/pest
```

Mock OpenRouter HTTP calls in tests — never hit the real API:

```php
Http::fake([
    'openrouter.ai/*' => Http::response([
        'choices' => [[
            'message' => ['content' => json_encode([
                'skills_matched' => ['PHP', 'Laravel'],
                // ... rest of shape
            ])]
        ]]
    ]),
]);
```

---

## Code style

```bash
./vendor/bin/pint        # fix
./vendor/bin/pint --test # check only
```

Laravel Pint with default Laravel ruleset. Run before committing.

---

## Extending the app

### Adding a new field to the AI analysis

1. Add the field to the prompt in `ResumeAnalysisService::buildPrompt()` (both the instruction and the JSON example)
2. Add a column via migration
3. Add the column to `ResumeAnalysis::$fillable` and add a `$cast` if it's JSON
4. Map it in `ResumeAnalysisRepository::create()`
5. Surface it in the Blade/Livewire view

### Adding a new Livewire component

1. `php artisan make:livewire MyComponent`
2. Register route in `routes/web.php` under the `auth` middleware group
3. Inject repositories via `boot()`, not `mount()` — Livewire calls `boot()` on every request but `mount()` only on first render, so constructor-style DI must go in `boot()`

### Adding a new repository

1. Define the interface in `app/Contracts/`
2. Implement in `app/Repositories/`
3. Bind in `AppServiceProvider::register()`:
   ```php
   $this->app->bind(MyInterface::class, MyRepository::class);
   ```

---

## Known issues / gotchas

**`most_recent_role` is not in `$fillable`** — `ResumeAnalysisRepository::create()` tries to set it but `ResumeAnalysis::$fillable` doesn't include it, so it silently drops. Add `'most_recent_role'` to the model's `$fillable` array.

**`grade` is a dead column** — it exists in `$fillable` and the migration but is never populated anywhere. Either wire it up or remove it to avoid confusion.

**AI field name mismatch** — the AI returns `skills_matched` / `skills_missing`, but the DB columns (and `$fillable`) are `core_skills` / `missing_skills`. The mapping happens in `ResumeAnalysisRepository::create()`. Don't rename one side without updating the other.

**OpenRouter timeout** — `callAI()` sets a 60-second timeout. On slow networks or large PDFs, this can still expire. The `ResumeSift` component catches all `\Exception`s and surfaces them as `$error` — the UI already handles it, but don't remove the try/catch.

**PDF text cap** — resumes longer than ~4 pages will be silently truncated to 6 000 chars before the AI sees them. This is intentional (token cost), but means skills mentioned late in a long resume may be missed.

---

## What to ignore

The `POST /chat` route is a standalone tool-calling experiment unrelated to resume analysis. Do not build on or reference it.

---
