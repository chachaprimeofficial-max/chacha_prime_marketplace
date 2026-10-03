<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request,Closure $next): Response
    {
        $user=$request->user();
        abort_unless($user,401);
        $isAdmin=DB::table('admin_user_roles as aur')
            ->join('admin_roles as ar','ar.id','=','aur.role_id')
            ->where('aur.user_id',$user->id)->exists();
        abort_unless($isAdmin,403,'Administrator access is required.');
        if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            DB::table('admin_audit_logs')->insert([
                'user_id'=>$user->id,
                'action'=>'admin_request',
                'entity_type'=>'route',
                'entity_id'=>null,
                'old_values'=>null,
                'new_values'=>json_encode(['route'=>$request->route()?->getName(),'method'=>$request->method()]),
                'ip_address'=>$request->ip(),
                'user_agent'=>$request->userAgent(),
                'created_at'=>now(),
            ]);
        }
        return $next($request);
    }
}