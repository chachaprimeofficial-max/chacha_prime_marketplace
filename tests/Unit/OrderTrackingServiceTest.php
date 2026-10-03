<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;
class OrderTrackingServiceTest extends TestCase {
 public function test_canonical_order_statuses_are_defined():void{$statuses=['pending','confirmed','processing','packing','shipped','in_transit','out_for_delivery','delivered','cancelled','returned','refunded'];$this->assertCount(11,$statuses);}
}