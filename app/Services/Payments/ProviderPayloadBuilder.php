<?php
namespace App\Services\Payments;
class ProviderPayloadBuilder
{
 public function build(array $payment):array
 {
  return [
   'merchant_reference'=>$payment['transaction_reference']??null,
   'order_id'=>$payment['order_id']??null,
   'amount'=>(float)($payment['amount']??0),
   'currency'=>$payment['currency']??'USD',
   'customer_id'=>$payment['user_id']??null,
   'success_url'=>url('/customer/payment/result/'.($payment['id']??'')),
   'cancel_url'=>url('/customer/payment/result/'.($payment['id']??'')),
   'webhook_url'=>url('/payments/webhook/'.($payment['provider']??'')),
  ];
 }
}
