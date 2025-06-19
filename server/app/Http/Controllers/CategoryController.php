<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\SearchCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
  public function search(SearchCategoryRequest $request): JsonResponse
  {
    $query = $request->validated();
    $page = $query['page'] ?? 1;
    $limit = $query['limit'] ?? 10;
    $q = $query['q'] ?? null;

    $query = Category::query()
      ->when($q, function ($query) use ($q) {
        $query->where('name', 'like', "%{$q}%");
      })
      ->orderBy('created_at', 'desc');

    $categories = $query->paginate($limit, ['*'], 'page', $page);

    if ($categories->isEmpty()) {
      Log::info('No categories found');
      return response()->json([
        'code' => 200,
        'message' => 'No categories found',
        'data' => [],
        'meta' => [
          'pageSize' => $limit,
          'totalItems' => 0,
          'currentPage' => $page,
          'totalPages' => 0
        ]
      ], 200);
    }

    Log::info('Categories retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Categories retrieved successfully',
      'data' => $categories,
      'meta' => [
        'pageSize' => $limit,
        'totalItems' => $categories->total(),
        'currentPage' => $page,
        'totalPages' => $categories->lastPage()
      ]
    ], 200);
  }

  public function list(): JsonResponse
  {
    $categories = Category::all();

    if ($categories->isEmpty()) {
      Log::info('No categories found');
      return response()->json([
        'code' => 200,
        'message' => 'No categories found',
        'data' => [],
      ], 200);
    }

    Log::info('Categories retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Categories retrieved successfully',
      'data' => $categories
    ], 200);
  }

  public function store(CreateCategoryRequest $request): Response
  {
    $fields = $request->validated();
    Category::create($fields);

    Log::info('Category created successfully');
    return response()->json([
      'code' => 201,
      'message' => 'Category created successfully'
    ], 201);
  }

  public function show(Category $category): JsonResponse
  {
    Log::info('Category retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Category retrieved successfully',
      'data' => $category
    ], 200);
  }

  public function update(Request $request, Category $category): JsonResponse
  {
    $fields = $request->validated();
    $category->update($fields);

    Log::info('Category updated successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Category updated successfully',
      'data' => $category
    ], 200);
  }

  public function delete(Category $category): Response
  {
    $category->delete();

    Log::info('Category deleted successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Category deleted successfully'
    ], 200);
  }
}
