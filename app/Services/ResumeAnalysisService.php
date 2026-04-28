<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

class ResumeAnalysisService
{
    public function analyze(string $jobDescription, string $resumeRealPath): array
    {
        $text = $this->extractText($resumeRealPath);
        $data = $this->callAI($jobDescription, $text);

        if (!is_array($data) || !isset($data['skills_matched'])) {
            throw new \RuntimeException('AI returned an invalid response. Please try again.');
        }

        return $data;
    }

    private function extractText(string $path): string
    {
        return mb_strimwidth((new Parser())->parseFile($path)->getText(), 0, 6000, '...');
    }

    private function callAI(string $jobDescription, string $resumeText): array
    {
        $response = Http::withToken(config('services.openrouter.key'))
            ->timeout(60)
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'           => 'anthropic/claude-3-haiku',
                'messages'        => [
                    ['role' => 'system', 'content' => 'Return ONLY valid JSON. No markdown, no explanation, no backticks.'],
                    ['role' => 'user',   'content' => $this->buildPrompt($jobDescription, $resumeText)],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature'     => 0.1,
                'max_tokens'      => 2000,
            ]);

        $data = $response->json('choices.0.message.content');

        if (is_string($data)) {
            $data = preg_replace('/^```json\s*|\s*```$/m', '', trim($data));
            $data = json_decode($data, true);
        }

        return is_array($data) ? $data : [];
    }

    private function buildPrompt(string $jd, string $resume): string
    {
        return <<<PROMPT
You are an expert HR assistant analyzing a candidate's resume against a job description.

SKILL MATCHING RULES:
1. Match skills based on semantic meaning, not exact strings.
2. A skill is MISSING only if no related term exists in the resume.
3. Use the job description's terminology in your response.

SKILL PROFICIENCY SCORING RULES:
For each skill the candidate HAS, assign a proficiency score 0-100 based on:
- 90-100: Expert (5+ years, led projects, deep expertise mentioned)
- 70-89: Proficient (3-5 years, built multiple projects)
- 40-69: Intermediate (1-3 years, used in a few projects)
- 0-39: Beginner (<1 year, basic knowledge, just listed)

JOB DESCRIPTION:
{$jd}

RESUME:
{$resume}

Return JSON ONLY:

{
  "candidate_name": "Full name from resume",
  "email": "Email from resume or null",
  "most_recent_role": "Most recent job title",
  "summary": "2-3 sentence professional summary",
  "skills_matched": ["Skills from JD that candidate HAS"],
  "skills_missing": ["Skills from JD that candidate DOES NOT have"],
  "skill_proficiency": { "SkillName": 85 },
  "work_experience": [
    { "company": "Company Name", "role": "Job Title", "start": "YYYY-MM", "end": "YYYY-MM or present" }
  ],
  "interview_questions": ["6 targeted questions based on skill gaps"],
  "recommendation": "Clear hire/maybe/pass recommendation with reasoning",
  "confidence_score": 0.85
}
PROMPT;
    }
}
