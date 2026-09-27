<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ChachaAiService
{
    public function answer(int $userId, string $message): array
    {
        $context = [
            'products' => DB::table('products')->select('id','title','sku','retail_price','wholesale_price','group_buying_price','stock_qty')->where('status','active')->limit(30)->get()->toArray(),
            'orders' => DB::table('orders')->select('id','order_number','status','tracking_number','courier')->where('user_id',$userId)->latest('id')->limit(10)->get()->toArray(),
        ];

        $system = 'You are Chacha, the Chacha Prime shopping assistant. Answer using supplied marketplace context. Never invent prices, stock, order status, policies or payment facts. If the issue involves fraud, payment disputes, account security, legal/safety concerns, or a problem you cannot resolve, set handoff=true and explain that a human agent will assist.';
        $key = config('services.gemini.api_key');
        if (!$key) return ['message'=>'Chacha is temporarily unavailable. Please contact a human agent.','handoff'=>true];

        $response = Http::timeout(30)->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key='.$key, [
            'system_instruction' => ['parts' => [['text' => $system]]],
            'contents' => [['role'=>'user','parts'=>[['text'=>"Marketplace context:\n".json_encode($context)."\n\nCustomer: ".$message]]]],
            'generationConfig' => ['temperature'=>0.2],
        ]);

        if (!$response->successful()) return ['message'=>'I could not reach Chacha AI right now. A human agent can help you.','handoff'=>true];
        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', 'Please contact a human agent for help.');
        $handoff = (bool) preg_match('/human agent|support agent|handoff|cannot resolve|payment dispute|fraud/i', $text);
        return ['message'=>$text,'handoff'=>$handoff];
    }
}
