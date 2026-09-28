<?php
namespace App\Services\Payments;
use App\Contracts\PaymentGatewayInterface;
class ConfiguredGateway implements PaymentGatewayInterface
{
 public function __construct(private string $provider){}
 private function key(string $suffix):string{return 'PAYMENT_'.strtoupper(preg_replace('/[^A-Za-z0-9]/','_',$this->provider)).'_'.$suffix;}
 public function createPayment(array $payment):array{$redirect=env($this->key('CHECKOUT_URL'));$callback=env($this->key('CALLBACK_URL'),url('/customer/payment/result/'.($payment['id']??'')));return ['status'=>'pending','provider'=>$this->provider,'reference'=>$payment['transaction_reference']??null,'redirect_url'=>$redirect,'callback_url'=>$callback];}
 public function verifyWebhook(array $headers,string $payload):bool{$secret=(string)env('PAYMENT_WEBHOOK_SECRET_'.strtoupper(preg_replace('/[^A-Za-z0-9]/','_',$this->provider)));if(!$secret)return false;$signature=(string)($headers['X-Webhook-Signature'][0]??$headers['x-webhook-signature'][0]??'');$signature=preg_replace('/^sha256=/','',$signature);return $signature!==''&&hash_equals(hash_hmac('sha256',$payload,$secret),$signature);}
 public function parseWebhook(string $payload):array{$data=json_decode($payload,true);return is_array($data)?$data:[];}
 public function refund(array $payment,float $amount):array{$url=env($this->key('REFUND_URL'));return ['status'=>'pending','provider'=>$this->provider,'amount'=>$amount,'reference'=>$payment['provider_reference']??null,'refund_url'=>$url];}
}
