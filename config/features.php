<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public registration
    |--------------------------------------------------------------------------
    |
    | Registration remains enabled for local development by default. Public
    | deployments can disable the guest registration routes in their .env.
    |
    */

    'public_registration' => (bool) env('PUBLIC_REGISTRATION_ENABLED', true),

];
