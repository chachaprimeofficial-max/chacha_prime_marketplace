<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ProductAiCompleter
{
    public function complete(array $product): array
    {
        $key = config('services.gemini.api_key');
        if (!$key) return ['status' => 'needs_review', 'missing' => $this->missing($product), 'fields' => []];

        $prompt = 'Complete missing ecommerce listing fields only when supported by the supplied product data. Never invent technical specifications, certifications, prices, stock, warranty or claims. Return JSON with fields: category, short_description, description, highlights, specifications, seo_title, seo_description, missing. Product: ' . json_encode($product, JSON_UNESCAPED_UNICODE);
        $response = Http::timeout(30)->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key='.$key, [
            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.15, 'responseMimeType' => 'application/json'],
        ]);

        if (!$response->successful()) return ['status' => 'needs_review', 'missing' => $this->missing($product), 'fields' => []];
        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '{}');
        $json = json_decode($text, true);
        if (!is_array($json)) return ['status' => 'needs_review', 'missing' => $this->missing($product), 'fields' => []];
        return ['status' => 'completed', 'missing' => $json['missing'] ?? [], 'fields' => $json];
    }

    private function missing(array $product): array
    {
        return array_values(array_filter(['description','highlights','specifications','category'], fn ($field) => empty($product[$field])));
    }
}
