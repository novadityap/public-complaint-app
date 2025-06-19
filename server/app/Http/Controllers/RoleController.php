<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Role\SearchRoleRequest;

class RoleController extends Controller
{
  public function search(SearchRoleRequest $request): JsonResponse
  {
    $query = $request->validated();
    $page = $query['page'] ?? 1;
    $limit = $query['limit'] ?? 10;
    $q = $query['q'] ?? null;

    $query = Role::query()
      ->when($q, function ($query) use ($q) {
        $query->where('name', 'like', "%{$q}%");
      })
      ->orderBy('created_at', 'desc');

    $roles = $query->paginate($limit, ['*'], 'page', $page);

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
      'data' => $roles->items(),
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
    $fields = $request->validated();
    Role::create($fields);

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
      'data' => $roles
    ], 200);
  }

  public function show(Role $role): JsonResponse
  {
    Log::info('Role retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Role retrieved successfully',
      'data' => $role
    ], 200);
  }

  public function update(UpdateRoleRequest $request, Role $role): JsonResponse
  {
    $fields = $request->validated();
    $role->update($fields);

    Log::info('Role updated successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Role updated successfully',
      'data' => $role
    ], 200);
  }

  public function delete(Role $role): Response
  {
    $role->delete();

    Log::info('Role deleted successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Role deleted successfully'
    ], 200);
  }
}
