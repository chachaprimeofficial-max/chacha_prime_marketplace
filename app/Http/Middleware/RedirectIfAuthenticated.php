<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class RedirectIfAuthenticated { public function handle(Request $request,Closure $next,...$guards){ if($request->user()) return redirect()->route('customer.dashboard'); return $next($request); } }