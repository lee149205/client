# Instruction

## Overview
- ZiraClient package for laravel.
- Check client token & generate token

## Requirements
- PHP >= 7.1.3

## Install
- Package is available on Packagist, you can install it using Composer.
    `composer require leetuanchinh/client`
- Edit config/app
 
   `'api_gateway_client_id' => env('API_GATEWAY_CLIENT_ID', null),
    'api_gateway_client_secret' => env('API_GATEWAY_CLIENT_SECRET', null),
    'api_org_client_id' => env('API_GATEWAY_CLIENT_ID', null),
    'api_org_client_secret' => env('API_GATEWAY_CLIENT_SECRET', null),
    'api_platform_client_id' => env('API_GATEWAY_CLIENT_ID', null),
    'api_platform_client_secret' => env('API_GATEWAY_CLIENT_SECRET', null),
    'api_tasklist_client_id' => env('API_GATEWAY_CLIENT_ID', null),
    'api_tasklist_client_secret' => env('API_GATEWAY_CLIENT_SECRET', null),
    'api_crm_client_id' => env('API_GATEWAY_CLIENT_ID', null),
    'api_crm_client_secret' => env('API_GATEWAY_CLIENT_SECRET', null),`

- Edit app/Http/Kernel.php
    
    `'client' => \ZiraClient\Middleware\CheckClientCredentials::class`
    
- If you want to create token to request to other app

    `$tokenService = new \ZiraClient\Services\Token();
     $authToken = $tokenService->generateToken($id, $secret, $userId);`