<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;

class AIProductController extends Controller
{
    public function generate(Request $request, OpenRouterService $ai)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = $request->input('name');
        $context = $request->input('context', []);

        $dropdownOptions = $request->input('dropdown_options', []);

        $prompt = $this->buildPrompt($name, $context, $dropdownOptions);

        $result = $ai->chat([
            [
                'role' => 'system',
                'content' => 'You are an expert e-commerce product content writer. You generate professional, SEO-friendly product content for a custom printing and photo product store called Qrinto. Always respond with valid JSON only, no markdown, no code fences, no explanation.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ]);

        $content = $result['choices'][0]['message']['content'] ?? '';

        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        $parsed = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'AI returned invalid JSON. Please try again.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $parsed,
        ]);
    }

    private function buildPrompt(string $name, array $context, array $dropdownOptions): string
    {
        $prompt = "Generate e-commerce product content for a product named: \"{$name}\".\n\n";

        if (!empty($context)) {
            $prompt .= "Existing form values (use as additional context, do not contradict them):\n";
            foreach ($context as $key => $value) {
                if (!empty($value)) {
                    $prompt .= "- {$key}: {$value}\n";
                }
            }
            $prompt .= "\n";
        }

        if (!empty($dropdownOptions)) {
            $prompt .= "Available dropdown options (you MUST pick from these exact values or leave empty):\n";
            foreach ($dropdownOptions as $field => $options) {
                $prompt .= "- {$field}: " . implode(', ', $options) . "\n";
            }
            $prompt .= "\n";
        }

        $prompt .= <<<EOT
Return a JSON object with these fields:
- "short_description": A 1-2 sentence product summary (max 200 chars)
- "description": A detailed HTML product description (3-5 paragraphs, use <p>, <ul>, <li>, <strong> tags)
- "meta_title": SEO meta title (max 60 chars)
- "meta_description": SEO meta description (max 160 chars)
- "base_price": A suggested reasonable price as a number (only if no price exists yet)
- "number_of_pages": Pick from available options or leave empty
- "event": Pick from available events or leave empty
- "store_visibility": Pick from available options or leave empty
- "category": Pick from available categories or leave empty
- "size": Pick from available sizes or leave empty
- "paper_type": Pick from available paper types or leave empty
- "tags": Array of 5-10 relevant SEO tags
- "pdf_type": Either "portrait" or "landscape"

Important rules:
- Only use values from the provided dropdown options
- If no good match exists for a dropdown, return an empty string
- Tags should be lowercase, relevant, and SEO-friendly
- Description should be professional and compelling
- Return ONLY valid JSON, no markdown or code fences
EOT;

        return $prompt;
    }
}
