<?php
namespace App\Services\Payments;
use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;
class PayPalGateway implements PaymentGatewayInterface
{
 private function base():string{return rtrim(env('PAYPAL_BASE_URL','https://api-m.sandbox.paypal.com'),'/');}
 private function token():string{$id=env('PAYPAL_CLIENT_ID');$secret=env('PAYPAL_CLIENT_SECRET');if(!$id||!$secret)throw new RuntimeException('PayPal credentials are not configured.');$r=Http::asForm()->withBasicAuth($id,$secret)->post($this->base().'/v1/oauth2/token',['grant_type'=>'client_credentials']);$r->throw();return (string)$r->json('access_token');}
 public function createPayment(array $payment):array{$token=$this->token();$amount=number_format((float)$payment['amount'],2,'.','');$currency=$payment['currency']??'USD';$return=url('/customer/payment/result/'.($payment['id']??''));$cancel=$return;$r=Http::withToken($token)->acceptJson()->post($this->base().'/v2/checkout/orders',['intent'=>'CAPTURE','purchase_units'=>[['reference_id'=>(string)($payment['transaction_reference']??$payment['id']),'amount'=>['currency_code'=>$currency,'value'=>$amount]]],'application_context'=>['return_url'=>$return,'cancel_url'=>$cancel,'user_action'=>'PAY_NOW']]);$r->throw();$approve=collect($r->json('links',[]))->first(fn($l)=>($l['rel']??'')==='approve');return ['status'=>'pending','provider'=>'paypal','reference'=>$r->json('id'),'redirect_url'=>$approve['href']??null,'provider_order_id'=>$r->json('id'),'raw'=>$r->json()];}
 public function verifyWebhook(array $headers,string $payload):bool{return false;}
 public function parseWebhook(string $payload):array{$d=json_decode($payload,true);return is_array($d)?$d:[];}
 public function refund(array $payment,float $amount):array{$token=$this->token();$captureId=$payment['provider_reference']??null;if(!$captureId)throw new RuntimeException('PayPal capture reference is required for refund.');$r=Http::withToken($token)->acceptJson()->post($this->base().'/v2/payments/captures/'.$captureId.'/refund',['amount'=>['value'=>number_format($amount,2,'.',''),'currency_code'=>$payment['currency']??'USD']]);$r->throw();return ['status'=>'pending','provider'=>'paypal','reference'=>$r->json('id'),'raw'=>$r->json()];}
}
