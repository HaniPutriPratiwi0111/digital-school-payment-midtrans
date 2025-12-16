<?php

return [

    'is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN),

    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),

    'api_url' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN) 
        ? 'https://api.midtrans.com/v2/' 
        : 'https://api.sandbox.midtrans.com/v2/',

    'snap_url' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN) 
        ? 'https://app.midtrans.com/snap/v1/' 
        : 'https://app.sandbox.midtrans.com/snap/v1/',
    
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL', env('APP_URL') . '/midtrans/notification'),

];
