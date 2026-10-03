<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function send(int $userId,string $title,string $message,string $type='system',?string $url=null): void
    {
        DB::table('notifications')->insert(['user_id'=>$userId,'type'=>$type,'title'=>$title,'message'=>$message,'action_url'=>$url,'created_at'=>now(),'updated_at'=>now()]);
    }
    public function orderStatus(int $userId,string $orderNumber,string $status,?string $url=null): void
    {
        $labels=['confirmed'=>'Order confirmed','processing'=>'Order processing','shipped'=>'Order shipped','delivered'=>'Order delivered','completed'=>'Order completed','cancelled'=>'Order cancelled'];
        $title=$labels[$status]??'Order status updated';
        $this->send($userId,$title,'Your order '.$orderNumber.' is now '.str_replace('_',' ',$status).'.','order',$url);
    }
    public function wallet(int $userId,string $message,?string $url=null): void{$this->send($userId,'Wallet update',$message,'wallet',$url);}
}
