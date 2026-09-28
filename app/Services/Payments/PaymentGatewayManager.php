<?php
namespace App\Services\Payments;
use App\Contracts\PaymentProvider;
use RuntimeException;
class PaymentGatewayManager
{
 public function provider(string $name): PaymentProvider
 {
  return match(strtolower($name)){
   'manual','cod'=>app(ManualGateway::class),
   default=>throw new RuntimeException('Payment provider is not configured: '.$name),
  };
 }
}
