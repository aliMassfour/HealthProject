<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="Your super Application API",
 *    version="8.1.6",
 * )
 * @OA\Server(
 *    url="http://127.0.0.1:8000/api",
 * )
 * @OA\SecurityScheme(
 *    securityScheme="bearerAuth",
 *    type="http",
 *    scheme="bearer",
 *    bearerFormat="JWT",
 * )
 */

class Controller extends BaseController
{


    use AuthorizesRequests, ValidatesRequests;
}
