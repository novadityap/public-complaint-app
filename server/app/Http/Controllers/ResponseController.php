<?php

namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ResponseResource;
use App\Http\Requests\Response\CreateResponseRequest;
use App\Http\Requests\Response\UpdateResponseRequest;

class ResponseController extends Controller
{
  public function list(Complaint $complaint): JsonResponse
  {
    $responses = $complaint->responses()
      ->with('user')
      ->orderBy('created_at', 'desc')
      ->get();

    if ($responses->isEmpty()) {
      return response()->json([
        'code' => 200,
        'message' => 'No responses found',
        'data' => []
      ], 200);
    }

    return response()->json([
      'code' => 200,
      'message' => 'Responses retrieved successfully',
      'data' => ResponseResource::collection($responses)
    ], 200);
  }

  public function show(Complaint $complaint, Response $response): JsonResponse {
    if ($complaint->id !== $response->complaint_id) {
      abort(404, 'Response not found for this complaint');
    }

    Log::info('Response retrieved successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Response retrieved successfully',
      'data' => new ResponseResource($response->load('complaint'))
    ]);
  }

  public function create(CreateResponseRequest $request, Complaint $complaint): JsonResponse
  {
    $fields = $request->validated();
    $fields['user_id'] = auth()->id();
    
    $complaint->update(['status' => $fields['status'] ]);
    unset($fields['status']);

    $complaint->responses()->create($fields);

    Log::info('Response created successfully');
    return response()->json([
      'code' => 201,
      'message' => 'Response created successfully'
    ], 201);
  }

  public function update(UpdateResponseRequest $request, Complaint $complaint, Response $response): JsonResponse
  {
     if ($complaint->id !== $response->complaint_id) {
      abort(404, 'Response not found for this complaint');
    }

    $fields = $request->validated();

    if (isset($fields['status'])) {
      $complaint->update(['status' => $fields['status'] ]);
      unset($fields['status']);
    };
    
    $fields['user_id'] = auth()->id();
    $response->update($fields);

    Log::info('Response updated successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Response updated successfully',
      'data' => new ResponseResource($response)
    ], 200);
  }

  public function delete(Complaint $complaint, Response $response): JsonResponse
  {
    if ($complaint->id !== $response->complaint_id) {
      abort(404, 'Response not found for this complaint');
    }

    $response->delete();

    Log::info('Response deleted successfully');
    return response()->json([
      'code' => 200,
      'message' => 'Response deleted successfully'
    ], 200);
  }
}
