<?php
namespace App\Http\Controllers;

use App\Services\CommerceQrService;
use Illuminate\Http\Request;

class QrController extends Controller
{
 public function show(string $type,int $id,Request $request,CommerceQrService $qr)
 {
  abort_unless(in_array($type,['product','invoice'],true),404);
  $record=$qr->resolve($type,$id);
  if($type==='invoice'){
   $invoice=\DB::table('invoices')->where('order_id',$id)->first();
   abort_unless($invoice,404);
   $payload=(string)($invoice->qr_payload?:url('/qr/invoice/'.$id));
  }else{
   $payload=$qr->product((int)$record->id,(string)$record->sku);
  }
  $size=max(120,min(1000,(int)$request->query('size',320)));
  $url='https://api.qrserver.com/v1/create-qr-code/?size='.$size.'x'.$size.'&format=png&data='.rawurlencode($payload);
  return redirect()->away($url);
 }
}