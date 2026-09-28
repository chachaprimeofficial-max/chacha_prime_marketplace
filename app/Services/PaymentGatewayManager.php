<?php
namespace App\Services;
use InvalidArgumentException;
class PaymentGatewayManager
{
 private const METHODS=['card','paypal','alipay','wechat_pay','easypaisa','jazzcash','raast','bank_transfer','lian_lian_pay','pingpong','wallet','cod'];
 public function supports(string $method):bool{return in_array($method,self::METHODS,true);}
 public function charge(string $method,int $orderId,float $amount,array $payload=[]):array{if(!$this->supports($method))throw new InvalidArgumentException('Unsupported payment method.');return ['status'=>'pending','provider'=>$method,'order_id'=>$orderId,'amount'=>$amount,'currency'=>$payload['currency']??config('chacha.brand.default_currency','USD')];}
 public function refund(string $method,string $transactionId,float $amount):array{if(!$this->supports($method))throw new InvalidArgumentException('Unsupported payment method.');return ['status'=>'pending','provider'=>$method,'transaction_id'=>$transactionId,'amount'=>$amount];}
 public function available():array{return self::METHODS;}
}
