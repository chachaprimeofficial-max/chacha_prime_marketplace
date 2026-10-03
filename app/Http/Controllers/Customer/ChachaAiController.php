<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Services\ChachaAiService;
use Illuminate\Http\Request;
class ChachaAiController extends Controller {
 public function index(){return view('customer.chacha-ai');}
 public function ask(Request $r,ChachaAiService $ai){$d=$r->validate(['message'=>'required|string|max:4000']);return response()->json($ai->answer($r->user()->id,$d['message']));}
}