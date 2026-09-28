<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
class TwoFactorService
{
 public function enable(int $userId,string $secret):void{if(strlen($secret)<16)throw new RuntimeException('Invalid 2FA secret.');DB::table('users')->where('id',$userId)->update(['two_factor_secret'=>encrypt($secret),'two_factor_enabled'=>1,'updated_at'=>now()]);}
 public function disable(int $userId):void{DB::table('users')->where('id',$userId)->update(['two_factor_secret'=>null,'two_factor_enabled'=>0,'two_factor_recovery_codes'=>null,'updated_at'=>now()]);}
 public function recoveryCodes(int $userId):array{$codes=collect(range(1,8))->map(fn()=>Str::upper(Str::random(10)))->all();DB::table('users')->where('id',$userId)->update(['two_factor_recovery_codes'=>encrypt(json_encode($codes)),'updated_at'=>now()]);return $codes;}
}
