<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllPostsCollection;
use App\Models\Post;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{

    public function index()
    {
        $posts = Post::where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->get();
        return Inertia::render('User', [
            'posts' => new AllPostsCollection($posts)
        ]);
    }

    public function show(int $id)
    {
        $posts = Post::where('user_id', $id)->orderBy('created_at', 'desc')->get();
        return Inertia::render('User', [
            'user' => User::find($id),
            'posts' => new AllPostsCollection($posts)
        ]);
    }

    public function updateImage(Request $request)
    {
        $request->validate(['image' => 'required|mimes:jpg,jpeg,png']);
        $user = (new ImageService)->updateImage(auth()->user(), $request);
        $user->save();
    }

    /**
     * @OA\Get(
     *      path="/api/users",
     *      operationId="getAllUsers",
     *      tags={"Users"},
     *      summary="Get all users",
     *      description="Returns a list of all users.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="users", type="array", @OA\Items(
     *                  @OA\Property(property="id", type="integer", format="int64"),
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="email", type="string", format="email"),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time"),
     *              ))
     *          )
     *      )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
        $users = User::all();
        return response()->json(['users' => $users]);
    }

    public function me()
    {
        $user = Auth::user();

        return $this->responseHelper->successResponse(true, 'Here is the the user', $user);
    }

    public function toggleFriend($id)
    {
        $userFriends = auth()->user()->friends();

        if ($userFriends->find($id)) {
            $userFriends->detach([$id]);
            return $this->responseHelper->successResponse(true, 'Removed Friend', []);
        }

        $userFriends->attach($id);

        return $this->responseHelper->successResponse(true, 'Added Friend', []);
    }

    public function addFriend($id)
    {
        $user = auth()->user();

        // Check if the user is already friends to avoid duplication
        if ($user->friends()->where('friend_id', $id)->exists()) {
            return response()->json(['error' => 'Already friends'], 400);
        }

        // Add the friend directly using attach
        $user->friends()->attach($id);

        // Optionally, you can load the friends relationship to include in the response
        $user->load('friends');

        return response()->json(['message' => 'Friend added successfully', 'user' => $user]);
    }

    /**
     * @OA\Get(
     *      path="/user/numberOfFriends",
     *      operationId="numberOfFriends",
     *      tags={"Users"},
     *      summary="Get the number of friends for the authenticated user",
     *      description="Returns the number of friends for the authenticated user.",
     *      security={
     *          {"sanctum": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="numberOfFriends", type="integer"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="error", type="string", example="Unauthenticated."),
     *          )
     *      )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function numberOfFriends(Request $request)
    {
        $user = $request->user();
        $friendCount = $user->friends()->count();

        return response()->json(['numberOfFriends' => $friendCount]);
    }
    public function getFriends()
    {
        $userFriends = auth()->user()->friends()->get();
        return $this->responseHelper->successResponse(true, 'All Friends', $userFriends);
    }
}
