<?php
namespace App\Services;
class InvoiceQrService
{
 public function payload(object $invoice): string
 {
  return json_encode(['invoice'=>$invoice->invoice_number,'order'=>$invoice->order_number??null,'total'=>(string)$invoice->total_amount,'currency'=>$invoice->currency,'issued_at'=>$invoice->issued_at],JSON_UNESCAPED_SLASHES);
 }
}
