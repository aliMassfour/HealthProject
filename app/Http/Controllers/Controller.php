<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
 /**
     * @OA\Info(
     *    title="Your super  ApplicationAPI",
     *    version="8.1.6",
     * )
     */
class Controller extends BaseController
{
   

    use AuthorizesRequests, ValidatesRequests;
}
