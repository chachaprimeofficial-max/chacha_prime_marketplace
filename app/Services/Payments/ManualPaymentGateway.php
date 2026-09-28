<?php
namespace App\Services\Payments;
use App\Contracts\PaymentGatewayInterface;
class ManualPaymentGateway implements PaymentGatewayInterface
{
 public function createPayment(array $payment): array{return ['status'=>'pending','reference'=>$payment['transaction_reference']??null,'redirect_url'=>null];}
 public function verifyWebhook(array $headers,string $payload): bool{return true;}
 public function parseWebhook(string $payload): array{$data=json_decode($payload,true);return is_array($data)?$data:[];}
 public function refund(array $payment,float $amount): array{return ['status'=>'pending','amount'=>$amount,'reference'=>$payment['provider_reference']??null];}
}
