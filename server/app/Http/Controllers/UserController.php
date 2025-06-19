<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\User\ProfileRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\SearchUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class UserController extends Controller
{
  public function search(SearchUserRequest $request): Response
  {
    $query = $request->validated();
    $page = $query['page'] ?? 1;
    $limit = $query['limit'] ?? 10;
    $q = $query['q'] ?? null;

    $query = User::query()
      ->with('role')
      ->where('id', '!=', auth()->user()->id)
      ->when($q, function ($query) use ($q) {
        $query->where(function ($subQuery) use ($q) {
          $subQuery->where('username', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orWhereHas('role', function ($roleQuery) use ($q) {
              $roleQuery->where('name', 'like', "%{$q}%");
            });
        });
      })
      ->orderBy('created_at', 'desc');

    $users = $query->paginate($limit, ['*'], 'page', $page);

    if ($users->isEmpty()) {
      Log::info('No users found');
      return response()->json([
        'code' => 200,
        'message' => 'No users found',
        'data' => [],
        'meta' => [
          'pageSize' => $limit,
          'totalItems' => 0,
          'currentPage' => $page,
          'totalPages' => 0
        ]
      ], 200);
    }

    Log::info('Users retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Users retrieved successfully',
      'data' => $users,
      'meta' => [
        'pageSize' => $limit,
        'totalItems' => $users->total(),
        'currentPage' => $page,
        'totalPages' => $users->lastPage()
      ]
    ], 200);
  }

  public function create(CreateUserRequest $request): Response
  {
    $fields = $request->validated();
    User::create($fields);

    Log::info('User created successfully');
    return response()->json([
      'code' => 201,
      'message' => 'User created successfully'
    ], 201);
  }

  public function show(User $user): JsonResponse
  {
    Log::info('User retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'User retrieved successfully',
      'data' => $user
    ], 200);
  }

  public function update(UpdateUserRequest $request, User $user): JsonResponse
  {
    Gate::authorize('update', $user);

    $fields = $request->validated();

    if ($request->hasFile('avatar')) {
      $uploadedFile = $request->file('avatar')->storeOnCloudinary('avatars');
      $fields['avatar'] = $uploadedFile->getSecurePath();
    }

    $user->update($fields);

    Log::info('User updated successfully');
    return response()->json([
      'code' => 200,
      'message' => 'User updated successfully',
      'data' => $user
    ], 200);
  }

  public function profile(ProfileRequest $request, User $user): JsonResponse
  {
    Gate::authorize('profile', $user);

    $fields = $request->validated();

    if ($request->hasFile('avatar')) {
      $uploadedFile = $request->file('avatar')->storeOnCloudinary('avatars');
      $fields['avatar'] = $uploadedFile->getSecurePath();
    }

    $user->update($fields);

    Log::info('Profile updated successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Profile updated successfully',
      'data' => $user
    ], 200);
  }

  public function delete(User $user): Response
  {
    $publicId = Cloudinary::extractPublicId($user->avatar);
    Cloudinary::destroy($publicId);
    
    $user->delete();

    Log::info('User deleted successfully');
    return response()->json([
      'code' => 200,
      'message' => 'User deleted successfully'
    ], 200);
  }
}
