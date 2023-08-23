<?php

namespace App\Http\Controllers\Title;

use App\Http\Controllers\Controller;
use App\Models\MainTitle;
use Illuminate\Http\Request;

class MainTitleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/maintitle",
     *     summary="Get all main titles",
     *     description="Endpoint to retrieve all main titles",
     *     tags={"Main Title"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="main_titles", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="Example Title"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-08-19T12:34:56Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-08-19T12:34:56Z")
     *                 )
     *             )
     *         )
     *     ),
      *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function index()
    {
        $main_titles = MainTitle::all();

        return response()->json([
            'main_titles' => $main_titles
        ]);
    }
    /**
     * @OA\Post(
     *     path="/maintitle",
     *     summary="Create a new main title",
     *     description="Endpoint to create a new main title",
     *     tags={"Main Title"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Example Title"),
     *             @OA\Property(property="en_name", type="string", example="Example Title")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Main title created successfully"),
     *             @OA\Property(
     *                 property="main_title",
     *                 type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="en_name", type="string")
     *             )
     *         )
     *     ),
      *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function store(Request $request)
    {
        
        $this->validate($request, [
            'name' => 'required',
            'en_name' => 'required'
        ]);
        $main_title = MainTitle::query()->create([
            'name' => $request->name ,
            'en_name' => $request->en_name
        ]);
        return response()->json([
            "message" => 'Main title created successfully',
            'main_title' => $main_title
        ]);
    }
}
