<?php

return [
    'publicKey' => env('FLUTTERWAVE_PUBLIC_KEY', env('FLW_PUBLIC_KEY')),
    'secretKey' => env('FLUTTERWAVE_SECRET_KEY', env('FLW_SECRET_KEY')),
    'secretHash' => env('FLUTTERWAVE_SECRET_HASH', env('FLW_SECRET_HASH', '')),
];
