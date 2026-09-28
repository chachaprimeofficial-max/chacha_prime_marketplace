<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class PaymentReconciliationService
{
 public function record(int $transactionId, string $providerStatus, string $systemStatus, float $difference=0, ?string $notes=null): int
 {
  return DB::table('payment_reconciliations')->insertGetId(['transaction_id'=>$transactionId,'provider_status'=>$providerStatus,'system_status'=>$systemStatus,'difference_amount'=>$difference,'status'=>abs($difference)<=0.01&&$providerStatus===$systemStatus?'matched':'mismatch','checked_at'=>now(),'notes'=>$notes,'created_at'=>now(),'updated_at'=>now()]);
 }
 public function reconcile(int $transactionId, string $providerStatus, float $providerAmount, string $providerCurrency): int
 {
  $tx=DB::table('payment_transactions')->where('id',$transactionId)->first();
  $difference=round($providerAmount-(float)$tx->amount,2);
  $notes=strtoupper($providerCurrency)!==strtoupper($tx->currency)?'Currency mismatch.':null;
  if($notes===null&&abs($difference)>0.01)$notes='Amount mismatch.';
  return $this->record($transactionId,$providerStatus,$tx->status,$difference,$notes);
 }
}
