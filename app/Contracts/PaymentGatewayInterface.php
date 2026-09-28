<?php
namespace App\Contracts;
interface PaymentGatewayInterface
{
 public function createPayment(array $payment): array;
 public function verifyWebhook(array $headers,string $payload): bool;
 public function parseWebhook(string $payload): array;
 public function refund(array $payment,float $amount): array;
}
