<?php

namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Response\CreateResponseRequest;
use App\Http\Requests\Response\UpdateResponseRequest;

class ResponseController extends Controller
{
    public function create(CreateResponseRequest $request, Complaint $complaint): Response
    {
        $fields = $request->validated();
        $fields['user_id'] = auth()->id();
        $complaint->responses()->create($fields);

        Log::info('Response created successfully');
        return response()->json([
          'code' => 200,
          'message'=> 'Response created successfully'
        ], 200);
    }

    public function update(UpdateResponseRequest $request, Complaint $complaint): JsonResponse
    {
      $fields = $request->validated();
      $fields['user_id'] = auth()->id();
      $response = $complaint->responses()->update($fields);

      Log::info('Response updated successfully');
      return response()->json([
        'code' => 200,
        'message'=> 'Response updated successfully',
        'data' => $response
      ], 200);
    }

    public function delete(Complaint $complaint, Response $response): JsonResponse
    {
      if ($complaint->id !== $response->complaint_id) abort(404, 'Response not found for this complaint');

      $response->delete();

      Log::info('Response deleted successfully');
      return response()->json([
        'code' => 200,
        'message'=> 'Response deleted successfully'
      ], 200);
    }
}
