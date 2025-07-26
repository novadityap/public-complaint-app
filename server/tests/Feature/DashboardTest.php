<?php

describe('GET /api/dashboard', function () {
  beforeEach(function () {
    createTestCategory();
    createTestUser();
    createTestComplaint();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestResponses();
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestRoles();
    removeAllTestCategories();
  });

  it('should return an error if user does not have permission', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->getJson('/api/dashboard', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return dashboard statistics data', function () {
    createManyTestCategories();
    createManyTestRoles();
    createManyTestComplaints();
    createManyTestUsers();
    createManyTestResponses();

    $result = $this->getJson('/api/dashboard', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Statistics data retrieved successfully');
    expect($result->json('data.totalComplaints'))->toBeGreaterThanOrEqual(15);
    expect($result->json('data.totalResponses'))->toBeGreaterThanOrEqual(15);
    expect($result->json('data.totalCategories'))->toBeGreaterThanOrEqual(15);
    expect($result->json('data.totalRoles'))->toBeGreaterThanOrEqual(15);
    expect($result->json('data.totalUsers'))->toBeGreaterThanOrEqual(15);
    expect($result->json('data.recentComplaints'))->toHaveCount(5);
    expect($result->json('data.recentResponses'))->toHaveCount(5);
  });
});
