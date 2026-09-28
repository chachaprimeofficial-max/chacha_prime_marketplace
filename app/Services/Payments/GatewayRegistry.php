<?php
namespace App\Services\Payments;
use App\Contracts\PaymentGatewayInterface;
class GatewayRegistry
{
 public function gateway(string $provider):PaymentGatewayInterface{return match($provider){ 'paypal'=>new PayPalGateway(), default=>new ConfiguredGateway($provider),};}
}
