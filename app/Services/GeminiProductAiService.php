<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProductAiService
{
    public function recommendCategory(array $product): array
    {
        $key = config('services.gemini.key');
        if (!$key) throw new RuntimeException('Gemini API key is not configured.');

        $prompt = "Return JSON only with keys category_name, category_path, confidence, missing_fields. Recommend a marketplace category for this product. Do not invent product facts. Product: " . json_encode($product, JSON_UNESCAPED_UNICODE);
        $response = Http::timeout(30)->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode($key), [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['responseMimeType' => 'application/json'],
        ]);
        if (!$response->successful()) throw new RuntimeException('Gemini category recommendation failed.');
        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        $result = json_decode($text ?: '{}', true);
        return is_array($result) ? $result : [];
    }
}
