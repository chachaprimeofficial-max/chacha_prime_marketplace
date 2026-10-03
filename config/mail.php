<?php
return [
 'default'=>env('MAIL_MAILER','smtp'),
 'mailers'=>[
  'smtp'=>[
   'transport'=>'smtp','host'=>env('MAIL_HOST','127.0.0.1'),'port'=>env('MAIL_PORT',587),
   'encryption'=>env('MAIL_ENCRYPTION','tls'),'username'=>env('MAIL_USERNAME'),'password'=>env('MAIL_PASSWORD'),'timeout'=>null,
  ],
  'log'=>['transport'=>'log'],
 ],
 'from'=>['address'=>env('MAIL_FROM_ADDRESS','no-reply@chachaprime.com'),'name'=>env('MAIL_FROM_NAME','Chacha Prime')],
];