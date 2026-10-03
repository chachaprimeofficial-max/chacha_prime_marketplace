<?php
return ['gemini'=>['api_key'=>env('GEMINI_API_KEY')],'paypal'=>['client_id'=>env('PAYPAL_CLIENT_ID'),'client_secret'=>env('PAYPAL_CLIENT_SECRET'),'mode'=>env('PAYPAL_MODE','sandbox')]];