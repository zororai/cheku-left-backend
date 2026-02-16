<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Cheku Left API',
    description: 'API documentation for Cheku Left POS system',
    contact: new OA\Contact(email: 'support@chekuleft.com')
)]
#[OA\Server(
    url: '/api',
    description: 'API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter your bearer token'
)]
abstract class Controller
{
    //
}
