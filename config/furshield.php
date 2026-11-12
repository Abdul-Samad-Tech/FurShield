<?php

return [

    'currency_symbol' => env('FURSHIELD_CURRENCY_SYMBOL', '$'),

    'support_phone' => env('FURSHIELD_SUPPORT_PHONE', '+92-21-111-387-743'),

    /*
    | No emergency number is hard-coded in the views. Leave this empty
    | unless FURSHIELD_EMERGENCY_PHONE is set.
    */
    'emergency_phone' => env('FURSHIELD_EMERGENCY_PHONE'),

];
