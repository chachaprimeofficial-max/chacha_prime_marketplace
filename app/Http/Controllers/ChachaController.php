<?php

namespace App\Http\Controllers;

use App\Services\ChachaAiService;
use Illuminate\Http\Request;

class ChachaController extends Controller
{
    public function ask(Request $request, ChachaAiService $chacha)
    {
        $data = $request->validate(['message'=>['required','string','max:4000']]);
        return response()->json($chacha->answer(auth()->id(), $data['message']));
    }
}
