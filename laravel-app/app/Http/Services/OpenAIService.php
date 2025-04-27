<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    protected $apiKey;
    protected $endpoint;
    protected $model;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
        $this->endpoint = 'https://api.openai.com/v1/chat/completions';
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
    }

    public function summarize(string $text): string
    {
        $prompt = <<<PROMPT
                    你是一位日記整理師，請幫我以條列式簡要整理以下文字內容，並且去做一個總結，最後統整我今天過得好不好(也可以適時地加入 emoji 讓摘要更活潑一些)：
                    $text
                    PROMPT;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2, // 小抖動，保持穩定
            'max_tokens' => 512,  // 適合中短篇摘要
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception('OpenAI API 回應失敗：' . $response->body());
    }
}
