<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;  // ← Tambahkan ini!
use Illuminate\Support\Facades\Log;

Artisan::command('test:gemini', function () {
    $apiKey = env('GEMINI_API_KEY');

    if (!$apiKey) {
        $this->error("❌ GEMINI_API_KEY tidak ditemukan di .env file");
        $this->info("Silakan tambahkan: GEMINI_API_KEY=your_api_key_here");
        return;
    }

    $this->info("🔍 Testing Gemini API...");
    $this->newLine();

    // List semua model yang tersedia
    $this->info("📋 Fetching available models...");
    $listResponse = Http::get("https://generativelanguage.googleapis.com/v1/models?key={$apiKey}");

    if ($listResponse->successful()) {
        $this->info("✅ Available models:");
        $models = $listResponse->json()['models'] ?? [];
        foreach ($models as $model) {
            $methods = implode(', ', $model['supportedGenerationMethods'] ?? []);
            if (str_contains($methods, 'generateContent')) {
                $this->line("   • " . $model['name']);
            }
        }
    } else {
        $this->error("❌ Failed to fetch models: " . $listResponse->body());
    }

    $this->newLine();

    // Test dengan berbagai model
    $modelsToTest = [
        'gemini-pro',
        'gemini-1.5-pro',
        'gemini-pro-vision'
    ];

    foreach ($modelsToTest as $model) {
        $this->info("🧪 Testing with {$model}...");

        $response = Http::withoutVerifying()  // Hanya untuk development
            ->timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Say "Hello" in Indonesian language. Just return the word only.']
                        ]
                    ]
                ]
            ]);

        if ($response->successful()) {
            $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
            $this->info("   ✅ Success: {$text}");
            $this->newLine();
            break; // Stop jika sudah berhasil
        } else {
            $error = $response->json()['error']['message'] ?? $response->body();
            $this->warn("   ⚠️ Failed: " . substr($error, 0, 100));
        }
    }

})->purpose('Test Gemini API connection');
