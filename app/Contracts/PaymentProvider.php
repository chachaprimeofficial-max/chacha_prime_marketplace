<?php
namespace App\Contracts;
interface PaymentProvider
{
 public function name(): string;
 public function createPayment(int $orderId,float $amount,string $currency,array $customer=[]): array;
 public function verify(string $reference,array $payload=[]): array;
 public function refund(string $reference,float $amount,array $metadata=[]): array;
}
