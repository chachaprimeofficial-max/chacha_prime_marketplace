<?php
return ['default'=>env('CACHE_STORE','file'),'stores'=>['file'=>['driver'=>'file','path'=>storage_path('framework/cache/data'),'serialize'=>true]],'prefix'=>env('CACHE_PREFIX','chacha_prime')];
