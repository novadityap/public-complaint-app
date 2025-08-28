<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Role\SearchRoleRequest;

class RoleController extends Controller
{
  public function search(SearchRoleRequest $request): JsonResponse
  {
    $page = $request->input('page');
    $limit = $request->input('limit');
    $q = $request->input('q');
    $sortBy = $request->input('sortBy');
    $sortOrder = $request->input('sortOrder');

    $roles = Role::query()
      ->when($q, function ($query) use ($q) {
        $query->where('name', 'ilike', "%{$q}%");
      })
      ->orderBy($sortBy, $sortOrder)
      ->paginate($limit, ['*'], 'page', $page);

    if ($roles->isEmpty()) {
      Log::info('No roles found');
      return response()->json([
        'code' => 200,
        'message' => 'No roles found',
        'data' => [],
        'meta' => [
          'pageSize' => $limit,
          'totalItems' => 0,
          'currentPage' => $page,
          'totalPages' => 0
        ]
      ], 200);
    }

    Log::info('Roles retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Roles retrieved successfully',
      'data' => RoleResource::collection($roles->items()),
      'meta' => [
        'pageSize' => $limit,
        'totalItems' => $roles->total(),
        'currentPage' => $page,
        'totalPages' => $roles->lastPage()
      ]
    ], 200);
  }
  
  public function create(CreateRoleRequest $request): JsonResponse
  {
    Role::create($request->validated());

    Log::info('Role created successfully');
    return response()->json([
      'code' => 201,
      'message' => 'Role created successfully'
    ], 201);
  }
  public function list(): JsonResponse
  {
    $roles = Role::all();

    if ($roles->isEmpty()) {
      Log::info('No roles found');
      return response()->json([
        'code' => 200,
        'message' => 'No roles found',
        'data' => [],
      ], 200);
    }

    Log::info('Roles retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Roles retrieved successfully',
      'data' => RoleResource::collection($roles)
    ], 200);
  }

  public function show(Role $role): JsonResponse
  {
    Log::info('Role retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Role retrieved successfully',
      'data' => new RoleResource($role)
    ], 200);
  }

  public function update(UpdateRoleRequest $request, Role $role): JsonResponse
  {
    $role->update($request->validated());

    Log::info('Role updated successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Role updated successfully',
      'data' => new RoleResource($role)
    ], 200);
  }

  public function delete(Role $role): JsonResponse
  {
    $role->delete();

    Log::info('Role deleted successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Role deleted successfully'
    ], 200);
  }
}
