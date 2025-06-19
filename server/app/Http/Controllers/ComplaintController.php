<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Complaint\ComplaintImageRequest;
use App\Http\Requests\Complaint\CreateComplaintRequest;
use App\Http\Requests\Complaint\SearchComplaintRequest;
use App\Http\Requests\Complaint\UpdateComplaintRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Http\Requests\Complaint\DeleteComplaintImageRequest;
use App\Http\Requests\Complaint\UploadComplaintImageRequest;

class ComplaintController extends Controller
{
  public function search(SearchComplaintRequest $request): JsonResponse
  {
    $user = auth()->user();
    $isAdmin = $user->role->name === 'admin';
    $query = $request->validated();
    $page = $query['page'] ?? 1;
    $limit = $query['limit'] ?? 10;
    $q = $query['q'] ?? null;

    $query = Complaint::query()
      ->with(['category', 'user'])
      ->when(!$isAdmin, function ($query) use ($user) {
        $query->where('user_id', $user->id);
      })
      ->when($q, function ($query) use ($q) {
        $query->where(function ($subQuery) use ($q) {
          $subQuery->where('title', 'like', "%{$q}%")
            ->orWhere('content', 'like', "%{$q}%")
            ->orWhere('status', 'like', "%{$q}%")
            ->orWhereHas('user', function ($userQuery) use ($q) {
              $userQuery->where('email', 'like', "%{$q}%");
            })
            ->orWhereHas('category', function ($categoryQuery) use ($q) {
              $categoryQuery->where('name', 'like', "%{$q}%");
            });
        });
      })
      ->orderBy('created_at', 'desc');

    $complaints = $query->paginate($limit, ['*'], 'page', $page);

    if ($complaints->isEmpty()) {
      Log::info('No complaints found');
      return response()->json([
        'code' => 200,
        'message' => 'No complaints found',
        'data' => [],
        'meta' => [
          'pageSize' => $limit,
          'totalItems' => 0,
          'currentPage' => $page,
          'totalPages' => 0
        ]
      ], 200);
    }

    Log::info('Complaints retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Complaints retrieved successfully',
      'data' => $complaints,
      'meta' => [
        'pageSize' => $limit,
        'totalItems' => $complaints->total(),
        'currentPage' => $page,
        'totalPages' => $complaints->lastPage()
      ]
    ], 200);
  }

  public function create(CreateComplaintRequest $request): Response
  {
    $fields = $request->validated();
    $imageUrls = [];

    if ($request->hasFile('images')) {
      foreach ($request->file('images') as $image) {
        $uploadedFile = $request->file('images')->storeOnCloudinary('complaints');
        $imageUrls[] = $uploadedFile->getSecurePath();
      }
    }

    Complaint::create([
      'user_id' => auth()->id(),
      'category_id' => $fields['category_id'],
      'title' => $fields['title'],
      'content' => $fields['content'],
      'status' => $fields['status'],
      'images' => $imageUrls,
    ]);

    Log::info('Complaint created successfully');
    return response()->json([
      'code' => 201,
      'message' => 'Complaint created successfully',
    ], 201);
  }

  public function show(Complaint $complaint): JsonResponse
  {
    Gate::authorize('show', $complaint);

    $complaint->load(['category', 'user']);

    Log::info('Complaint retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Complaint retrieved successfully',
      'data' => $complaint
    ], 200);
  }

  public function update(UpdateComplaintRequest $request, Complaint $complaint): JsonResponse
  {
    Gate::authorize('update', $complaint);

    $fields = $request->validated();
    $newImageUrls = [];

    $newImages = $request->file('images') ?? [];
    $totalImages = count($complaint->images) + count($newImages);

    if ($totalImages > 5)
      throw ValidationException::withMessages([
        'images' => 'You can upload a maximum of 5 images'
      ]);

    if ($newImages) {
      foreach ($newImages as $image) {
        $uploadedFile = $image->storeOnCloudinary('complaints');
        $newImageUrls[] = $uploadedFile->getSecurePath();
      }
    }

    $imagesToDelete = array_filter($complaint->images, function ($image) use ($newImageUrls) {
      return !in_array($image, $newImageUrls);
    });

    foreach ($imagesToDelete as $image) {
      $publicId = Cloudinary::extractPublicId($image);
      Cloudinary::destroy($publicId);
    }

    $fields['images'] = $newImageUrls;
    $complaint->update($fields);
    $complaint->load(['category', 'user']);

    Log::info('Complaint updated successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Complaint updated successfully',
      'data' => $complaint
    ], 200);
  }

  public function delete(Complaint $complaint): JsonResponse
  {
    Gate::authorize('delete', $complaint);

    foreach ($complaint->images as $image) {
      $publicId = Cloudinary::extractPublicId($image);
      Cloudinary::destroy($publicId);
    }

    $complaint->delete();

    Log::info('Complaint deleted successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Complaint deleted successfully'
    ], 200);
  }

  public function uploadImage(UploadComplaintImageRequest $request, Complaint $complaint): JsonResponse
  {
    $newImageUrls = [];
    $newImages = $request->file('images') ?? [];
    $totalImages = count($complaint->images) + count($newImages);

    if ($totalImages > 5) {
      throw ValidationException::withMessages([
        'images' => 'You can upload a maximum of 5 images'
      ]);
    }

    if ($newImages) {
      foreach ($newImages as $image) {
        $uploadedFile = $image->storeOnCloudinary('complaints');
        $newImageUrls[] = $uploadedFile->getSecurePath();
      }
    }

    $complaint->update([
      'images' => array_merge($complaint->images, $newImageUrls)
    ]);

    Log::info('Complaint image uploaded successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Complaint image uploaded successfully',
      'data' => $complaint
    ], 200);
  }

  public function deleteImage(DeleteComplaintImageRequest $request, Complaint $complaint): JsonResponse
  {
    $fields = $request->validated();

    if (!in_array($fields['image'], $complaint->images))
      abort(404, 'Complaint image not found');

    $publicId = Cloudinary::extractPublicId($fields['image']);
    Cloudinary::destroy($publicId);

    $newImages = array_filter($complaint->images, fn($image) => $image !== $fields['image']);
    $complaint->update(['images' => $newImages]);

    Log::info('Complaint image deleted successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Complaint image deleted successfully'
    ], 200);
  }
}
