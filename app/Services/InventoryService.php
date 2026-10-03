<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function deduct(int $productId, ?int $variantId, int $quantity, string $referenceType, int $referenceId, ?int $userId = null): void
    {
        if ($quantity < 1) throw new RuntimeException('Invalid inventory quantity.');
        $table = $variantId ? 'product_variants' : 'products';
        $id = $variantId ?: $productId;
        $row = DB::table($table)->where('id',$id)->lockForUpdate()->first();
        if (!$row) throw new RuntimeException('Inventory item not found.');
        $before=(int)$row->stock_qty;
        $after=$before-$quantity;
        if ($after<0) throw new RuntimeException('Insufficient stock.');
        DB::table($table)->where('id',$id)->update(['stock_qty'=>$after,'updated_at'=>now()]);
        if($variantId) DB::table('products')->where('id',$productId)->decrement('stock_qty',$quantity);
        DB::table('inventory_movements')->insert(['product_id'=>$productId,'variant_id'=>$variantId,'type'=>'stock_out','quantity'=>-$quantity,'quantity_before'=>$before,'quantity_after'=>$after,'reference_type'=>$referenceType,'reference_id'=>$referenceId,'created_by'=>$userId,'created_at'=>now(),'updated_at'=>now()]);
        $this->syncAlert($productId,$variantId,$after,(int)($row->low_stock_threshold ?? DB::table('products')->where('id',$productId)->value('low_stock_threshold') ?? 5));
    }
    public function restore(int $productId, ?int $variantId, int $quantity, string $type, string $referenceType, int $referenceId, ?int $userId = null): void
    {
        if ($quantity < 1) return;
        $table=$variantId?'product_variants':'products';$id=$variantId?:$productId;
        $row=DB::table($table)->where('id',$id)->lockForUpdate()->first();if(!$row)return;
        $before=(int)$row->stock_qty;$after=$before+$quantity;
        DB::table($table)->where('id',$id)->update(['stock_qty'=>$after,'updated_at'=>now()]);
        if($variantId) DB::table('products')->where('id',$productId)->increment('stock_qty',$quantity);
        DB::table('inventory_movements')->insert(['product_id'=>$productId,'variant_id'=>$variantId,'type'=>$type,'quantity'=>$quantity,'quantity_before'=>$before,'quantity_after'=>$after,'reference_type'=>$referenceType,'reference_id'=>$referenceId,'created_by'=>$userId,'created_at'=>now(),'updated_at'=>now()]);
        DB::table('inventory_alerts')->where('product_id',$productId)->where('status','open')->update(['status'=>'resolved','resolved_at'=>now()]);
    }
    private function syncAlert(int $productId, ?int $variantId, int $stock, int $threshold): void
    {
        if($stock>$threshold){DB::table('inventory_alerts')->where('product_id',$productId)->where('status','open')->update(['status'=>'resolved','resolved_at'=>now()]);return;}
        $type=$stock===0?'out_of_stock':'low_stock';
        $exists=DB::table('inventory_alerts')->where('product_id',$productId)->where('variant_id',$variantId)->where('alert_type',$type)->where('status','open')->exists();
        if(!$exists)DB::table('inventory_alerts')->insert(['product_id'=>$productId,'variant_id'=>$variantId,'alert_type'=>$type,'status'=>'open','created_at'=>now()]);
    }
}
