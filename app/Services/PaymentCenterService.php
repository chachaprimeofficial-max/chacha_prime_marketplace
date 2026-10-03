<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentCenterService
{
    // Canonical payment record: payments. payment_transactions is legacy and is no longer written.
    public function create(int $orderId,?int $userId,string $provider,float $amount,string $currency='USD',?int $methodId=null): int
    {
        if($amount<=0) throw new RuntimeException('Payment amount must be greater than zero.');
        $order=DB::table('orders')->where('id',$orderId)->first();
        if(!$order) throw new RuntimeException('Order not found.');
        return app(PaymentService::class)->recordPending($orderId,$provider,$provider,$amount,null);
    }

    public function createForCheckout(int $orderId,int $userId,string $provider,float $amount,string $currency='USD'): int
    {
        $method=DB::table('payment_methods')->where('provider',$provider)->where('enabled',1)->first();
        if(!$method) throw new RuntimeException('Selected payment method is unavailable.');
        return app(PaymentService::class)->recordPending($orderId,$provider,(string)$method->name,$amount,null);
    }

    public function updateStatus(int $id,string $status,?string $providerReference=null,?string $failureCode=null,?string $failureMessage=null): void
    {
        $service=app(PaymentService::class);
        if($status==='paid') $service->markPaid($id,$providerReference);
        elseif($status==='failed') $service->markFailed($id,$failureMessage);
        elseif($status==='refunded') {
            $payment=DB::table('payments')->where('id',$id)->firstOrFail();
            $remaining=max(0,(float)$payment->amount-(float)DB::table('payment_refunds')->where('payment_id',$id)->where('status','completed')->sum('amount'));
            if($remaining>0) $service->refund($id,$remaining,$failureMessage);
        } elseif(!in_array($status,['pending','authorized','cancelled','partially_refunded'],true)) {
            throw new RuntimeException('Invalid payment status.');
        }
    }

    public function receiveWebhook(string $provider,string $eventId,?string $eventType,array $payload): int
    {
        $existing=DB::table('payment_webhooks')->where('provider',$provider)->where('event_id',$eventId)->first();
        if($existing) return (int)$existing->id;
        return DB::table('payment_webhooks')->insertGetId([
            'provider'=>$provider,'event_id'=>$eventId,'event_type'=>$eventType,
            'payload'=>json_encode($payload),'status'=>'received','created_at'=>now(),'updated_at'=>now()
        ]);
    }
}