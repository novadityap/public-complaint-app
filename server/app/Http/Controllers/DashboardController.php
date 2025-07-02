<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\Response;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ResponseResource;
use App\Http\Resources\ComplaintResource;

class DashboardController extends Controller
{
  public function stats(): JsonResponse
  {
    $totalUsers = User::count();
    $totalRoles = Role::count();
    $totalCategories = Category::count();
    $totalComplaints = Complaint::count();
    $totalResponses = Response::count();

    $recentComplaints = Complaint::with(['user:id,email', 'category:id,name'])
      ->orderByDesc('created_at')
      ->take(5)
      ->get();

    $recentResponses = Response::with(['user:id,email', 'complaint:id,subject'])
      ->orderByDesc('created_at')
      ->take(5)
      ->get();

    Log::info('Statistics data retrieved successfully');

    return response()->json([
      'code' => 200,
      'message' => 'Statistics data retrieved successfully',
      'data' => [
        'totalUsers' => $totalUsers,
        'totalRoles' => $totalRoles,
        'totalCategories' => $totalCategories,
        'totalComplaints' => $totalComplaints,
        'totalResponses' => $totalResponses,
        'recentComplaints' =>  ComplaintResource::collection($recentComplaints),
        'recentResponses' => ResponseResource::collection($recentResponses),
      ],
    ]);
  }
}
