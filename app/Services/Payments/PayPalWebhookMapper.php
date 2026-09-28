<?php
namespace App\Services\Payments;
class PayPalWebhookMapper
{
 public function normalize(array $event): array
 {
  $resource=$event['resource']??[];
  $eventType=(string)($event['event_type']??'');
  $status=match($eventType){'PAYMENT.CAPTURE.COMPLETED','CHECKOUT.ORDER.COMPLETED'=>'paid','PAYMENT.CAPTURE.PENDING'=>'pending','PAYMENT.CAPTURE.DENIED'=>'failed','PAYMENT.CAPTURE.DECLINED'=>'failed','PAYMENT.CAPTURE.REFUNDED'=>'refunded',default=>null};
  $reference=$resource['id']??null;
  $custom=$resource['custom_id']??$resource['invoice_id']??null;
  $amount=$resource['amount']['value']??$resource['purchase_units'][0]['amount']['value']??null;
  $currency=$resource['amount']['currency_code']??$resource['purchase_units'][0]['amount']['currency_code']??null;
  return ['status'=>$status,'provider_reference'=>$reference,'transaction_reference'=>$custom,'amount'=>$amount!==null?(float)$amount:null,'currency'=>$currency,'event_type'=>$eventType];
 }
}
