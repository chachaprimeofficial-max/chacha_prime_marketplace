<?php
namespace App\Services\Payments;
use App\Contracts\PaymentProvider;
use RuntimeException;
class ManualGateway implements PaymentProvider
{
 public function name(): string{return 'manual';}
 public function createPayment(int $orderId,float $amount,string $currency,array $customer=[]): array{return ['status'=>'pending','reference'=>'MANUAL-'.$orderId.'-'.bin2hex(random_bytes(5)),'redirect_url'=>null,'instructions'=>'Payment must be verified by an administrator.'];}
 public function verify(string $reference,array $payload=[]): array{if(empty($payload['approved']))throw new RuntimeException('Manual payment is not approved.');return ['status'=>'paid','reference'=>$reference];}
 public function refund(string $reference,float $amount,array $metadata=[]): array{return ['status'=>'pending','reference'=>$reference,'amount'=>$amount,'message'=>'Refund queued for provider processing.'];}
}
