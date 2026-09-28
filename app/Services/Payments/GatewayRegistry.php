<?php
namespace App\Services\Payments;
use App\Contracts\PaymentGatewayInterface;
class GatewayRegistry
{
 public function gateway(string $provider):PaymentGatewayInterface{return new ConfiguredGateway($provider);}
}
