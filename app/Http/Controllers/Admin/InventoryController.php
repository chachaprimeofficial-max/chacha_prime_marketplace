<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class InventoryController extends Controller
{
 public function index(Request $r){$q=DB::table('products');if($r->filled('search'))$q->where(function($x)use($r){$x->where('title','like','%'.$r->search.'%')->orWhere('sku','like','%'.$r->search.'%');});$products=$q->orderBy('stock_qty')->paginate(25)->withQueryString();return view('admin.inventory.index',compact('products'));}
 public function adjust(Request $r,int $product){$d=$r->validate(['quantity'=>'required|integer|min:-100000|max:100000','note'=>'nullable|string|max:500']);DB::transaction(function()use($r,$product,$d){$p=DB::table('products')->where('id',$product)->lockForUpdate()->firstOrFail();$before=(int)$p->stock_qty;$after=$before+(int)$d['quantity'];abort_if($after<0,422,'Stock cannot be negative.');DB::table('products')->where('id',$product)->update(['stock_qty'=>$after,'updated_at'=>now()]);DB::table('inventory_movements')->insert(['product_id'=>$product,'type'=>'adjustment','quantity'=>$d['quantity'],'quantity_before'=>$before,'quantity_after'=>$after,'note'=>$d['note']??null,'reference_type'=>'admin_adjustment','reference_id'=>$product,'created_by'=>$r->user()->id,'created_at'=>now(),'updated_at'=>now()]);DB::table('inventory_alerts')->where('product_id',$product)->where('status','open')->update(['status'=>'resolved','resolved_at'=>now()]);if($after<=(int)$p->low_stock_threshold){DB::table('inventory_alerts')->insert(['product_id'=>$product,'alert_type'=>$after===0?'out_of_stock':'low_stock','status'=>'open','created_at'=>now()]);}});return back()->with('success','Inventory adjusted.');}
 public function history(int $product){$product=DB::table('products')->where('id',$product)->firstOrFail();$movements=DB::table('inventory_movements')->where('product_id',$product->id)->latest('id')->paginate(40);return view('admin.inventory.history',compact('product','movements'));}
}
