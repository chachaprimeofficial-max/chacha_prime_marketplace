<?php
namespace Tests\Unit;
use App\Services\CheckoutService;
use PHPUnit\Framework\TestCase;
class CheckoutServiceTest extends TestCase {
 public function test_checkout_calculation_applies_wallet_after_discount():void{$r=(new CheckoutService)->calculate([['price'=>100,'quantity'=>2]],10,20,50);$this->assertSame(200.0,$r['subtotal']);$this->assertSame(50.0,$r['wallet']);$this->assertSame(140.0,$r['total']);}
}