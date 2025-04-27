<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AudioController extends Controller
{
    public function transcribe(Request $request)
    {
        if (!$request->hasFile('audio')) {
            return response()->json(['error' => '沒有接收到音訊檔案'], 400);
        }

        $file = $request->file('audio');
        try {
            $model = $request->header('X-Model', 'base'); // 預設用 base 模型

            $response = Http::timeout(6000)->attach(
                'audio',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post('http://whisper:5000/transcribe?model=' . $model);

            if ($response->failed()) {
                return response()->json(['error' => 'Whisper 轉換失敗', 'details' => $response->body()], 500);
            }

            return response()->json(['transcript' => $response->json('transcript')]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Whisper 呼叫失敗', 'details' => $e->getMessage()], 500);
        }
    }

    public function summarize(Request $request)
    {
        $doc = data_get($request, 'doc');
        $provider = data_get($request, 'provider', 'gpt'); // 預設走 taide

        if (empty($doc)) {
            return response()->json(['error' => '缺少逐字稿內容，無法進行重點整理'], 400);
        }

        try {
            if ($provider === 'taide') {
                // 發送到本地 TAIDE Container
                $response = Http::timeout(6000)->post('http://taide:5000/summarize', [
                    'text' => $doc,
                ]);

                if ($response->successful()) {
                    $summary = data_get($response->json(), 'summary');

                    if (empty($summary)) {
                        return response()->json(['error' => 'TAIDE 回傳了空的摘要'], 500);
                    }

                    return response()->json([
                        'summary' => $summary,
                    ]);
                } else {
                    return response()->json([
                        'error' => 'TAIDE 回應失敗',
                        'details' => $response->body(),
                    ], $response->status());
                }
            } elseif ($provider === 'gpt') {
                // 呼叫 OpenAI GPT API（透過 Service）
                $openAIService = new OpenAIService();
                $summary = $openAIService->summarize($doc);

                return response()->json([
                    'summary' => $summary,
                ]);
            } else {
                return response()->json([
                    'error' => '不支援的摘要服務提供者',
                    'details' => "請傳入 'taide' 或 'gpt'",
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => '摘要過程中發生錯誤',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
