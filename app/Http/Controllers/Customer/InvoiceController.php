<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
class InvoiceController extends Controller
{
 public function show(int $order){$invoice=DB::table('invoices')->join('orders','orders.id','=','invoices.order_id')->where('invoices.order_id',$order)->where('orders.user_id',auth()->id())->select('invoices.*','orders.order_number')->firstOrFail();$items=DB::table('invoice_items')->where('invoice_id',$invoice->id)->get();return view('customer.invoices.show',compact('invoice','items'));}
 public function print(int $order){$invoice=DB::table('invoices')->join('orders','orders.id','=','invoices.order_id')->where('invoices.order_id',$order)->where('orders.user_id',auth()->id())->select('invoices.*','orders.order_number')->firstOrFail();$items=DB::table('invoice_items')->where('invoice_id',$invoice->id)->get();return view('customer.invoices.print',compact('invoice','items'));}
 public function generate(int $order){$o=DB::table('orders')->where('id',$order)->where('user_id',auth()->id())->firstOrFail();app(InvoiceService::class)->createForOrder($o->id);return redirect()->route('customer.invoices.show',$order);}
}
