<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\SearchCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;

class CategoryController extends Controller
{
  public function search(SearchCategoryRequest $request): JsonResponse
  {
    $page = $request->input('page');
    $limit = $request->input('limit');
    $q = $request->input('q');
    $sortBy = $request->input('sortBy');
    $sortOrder = $request->input('sortOrder');

    $categories = Category::query()
      ->when($q, function ($query) use ($q) {
        $query->where('name', 'ilike', "%{$q}%");
      })
      ->orderBy($sortBy, $sortOrder)
      ->paginate($limit, ['*'], 'page', $page);

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
      'data' => CategoryResource::collection($categories->items()),
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
      'data' => CategoryResource::collection($categories)
    ], 200);
  }

  public function create(CreateCategoryRequest $request): JsonResponse
  {
    Category::create($request->validated());

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
      'data' => new CategoryResource($category)
    ], 200);
  }

  public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
  {
    $category->update($request->validated());

    Log::info('Category updated successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Category updated successfully',
      'data' => new CategoryResource($category)
    ], 200);
  }

  public function delete(Category $category): JsonResponse
  {
    $category->delete();

    Log::info('Category deleted successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Category deleted successfully'
    ], 200);
  }
}
