<?php

namespace App\Http\Controllers\Title;

use App\Http\Controllers\Controller;
use App\Models\MainTitle;
use App\Models\SubTitle;
use Illuminate\Http\Request;

class SubTitleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/subtitle/{main_title}",
     *     summary="Get all sub titles",
     *     description="Endpoint to retrieve all sub titles",
     *     tags={"Sub Title"},
     *       @OA\Parameter(
     *         name="main_title",
     *         in="path",
     *         description="ID of the main title",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="sub_titles", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="int"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                      @OA\Property(property="en_name", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *    security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function index(MainTitle $main_title)
    {
        $sub_titles = $main_title->SubTitles;
        return response()->json([
            'sub_titles' => $sub_titles
        ]);
    }
    /**
     * @OA\Post(
     *     path="/subtitle/{main_title}",
     *     summary="Create a new sub title",
     *     description="Endpoint to create a new sub title under a main title",
     *     tags={"Sub Title"},
     *     @OA\Parameter(
     *         name="main_title",
     *         in="path",
     *         description="ID of the main title",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Example Sub Title")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sub title created successfully"),
     *             @OA\Property(
     *                 property="sub_title",
     *                 type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    public function store(Request $request, MainTitle $main_title)
    {
        $this->validate($request, [
            'name' => 'required',
            'en_name' => 'required'
        ]);
        $sub_title = $main_title->SubTitles()->create([
            'name' => $request->name,
            'en_name' => $request->en_name
        ]);
        return response()->json([
            'message' => 'sub title created successfully',
            'sub_title' => $sub_title
        ]);
    }
}
