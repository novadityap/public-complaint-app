<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Helpers\CloudinaryHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\ProfileRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\SearchUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

class UserController extends Controller
{
  public function search(SearchUserRequest $request): JsonResponse
  {
    $page = $request->input('page');
    $limit = $request->input('limit');
    $q = $request->input('q');
    $sortBy = $request->input('sortBy');
    $sortOrder = $request->input('sortOrder');

    $users = User::query()
      ->select('users.*')
      ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
      ->with('role')
      ->where('users.id', '!=', auth()->user()->id)
      ->when($q, function ($query) use ($q) {
        $query->where(function ($subQuery) use ($q) {
          $subQuery->where('username', 'ilike', "%{$q}%")
            ->orWhere('email', 'ilike', "%{$q}%")
            ->orWhereHas('role', function ($roleQuery) use ($q) {
              $roleQuery->where('name', 'ilike', "%{$q}%");
            });
        });
      })
      ->when($sortBy, function ($query) use ($sortBy, $sortOrder) {
        if ($sortBy === 'role.name') {
          $query->orderBy('roles.name', $sortOrder);
        } else {
          $query->orderBy("users.$sortBy", $sortOrder);
        }
      })
      ->paginate($limit, ['*'], 'page', $page);

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
      'data' => UserResource::collection($users->items()),
      'meta' => [
        'pageSize' => $limit,
        'totalItems' => $users->total(),
        'currentPage' => $page,
        'totalPages' => $users->lastPage()
      ]
    ], 200);
  }

  public function create(CreateUserRequest $request): JsonResponse
  {
    $fields = $request->validated();
    $fields['password'] = Hash::make($fields['password']);
    $fields['is_verified'] = true;

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
      'data' => new UserResource($user)
    ], 200);
  }

  public function update(UpdateUserRequest $request, User $user): JsonResponse
  {
    $fields = $request->validated();

    if (isset($fields['password'])) {
      $fields['password'] = Hash::make($fields['password']);
    }

    if ($request->hasFile('avatar')) {
      $uploadedFile = cloudinary()->uploadApi()->upload($request->file('avatar')->getRealPath(), ['folder' => 'avatars']);
      $fields['avatar'] = $uploadedFile['secure_url'];
      $this->deleteAvatar($user->avatar);
    }

    $user->update($fields);

    Log::info('User updated successfully');
    return response()->json([
      'code' => 200,
      'message' => 'User updated successfully',
      'data' => new UserResource($user)
    ], 200);
  }

  public function profile(ProfileRequest $request, User $user): JsonResponse
  {
    $fields = $request->validated();

    if (isset($fields['password'])) {
      $fields['password'] = Hash::make($fields['password']);
    }

    if ($request->hasFile('avatar')) {
      $uploadedFile = cloudinary()->uploadApi()->upload($request->file('avatar')->getRealPath(), ['folder' => 'avatars']);
      $fields['avatar'] = $uploadedFile['secure_url'];
      $this->deleteAvatar($user->avatar);
    }

    $user->update($fields);

    Log::info('Profile updated successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Profile updated successfully',
      'data' => new UserResource($user)
    ], 200);
  }

  public function delete(User $user): JsonResponse
  {
    $this->deleteAvatar($user->avatar);
    $user->delete();

    Log::info('User deleted successfully');
    return response()->json([
      'code' => 200,
      'message' => 'User deleted successfully'
    ], 200);
  }

  protected function deleteAvatar(string $avatarUrl): void
  {
    if (config('app.default_avatar_url') !== $avatarUrl) {
      cloudinary()->uploadApi()->destroy(CloudinaryHelper::extractPublicId($avatarUrl));
      Log::info('Avatar deleted successfully');
    }
  }
}
