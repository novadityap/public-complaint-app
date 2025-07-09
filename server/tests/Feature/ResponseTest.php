<?php

describe('GET /api/complaints/{complaint}/responses', function () {
  it('should return all responses', function () {
    createTestUser();
    createTestCategory();
    createTestComplaint();
    createManyTestResponses();
    createAccessToken();

    $complaint = getTestComplaint();
    $result = $this->getJson("/api/complaints/{$complaint->id}/responses", [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Responses retrieved successfully');

    removeAllTestResponses();
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
  });
});

describe('GET /api/complaints/{complaint}/responses/{response}', function () {
  beforeEach(function () {
    createTestUser();
    createTestCategory();
    createTestComplaint();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestResponses();
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
  });

  it('should return an error if response is not found', function () {
    $complaint = getTestComplaint();
    $result = $this->getJson("/api/complaints/{$complaint->id}/responses/" . test()->validUUID, [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Response not found');
  });

  it('should return a response if response id is valid', function () {
    $complaint = getTestComplaint();
    $response = createTestResponse();

    $result = $this->getJson("/api/complaints/{$complaint->id}/responses/{$response->id}", [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Response retrieved successfully');
    expect($result->json('data'))->toBeArray();
  });
});

describe('POST /api/complaints/{complaint}/responses', function () {
  beforeEach(function () {
    createTestUser();
    createTestCategory();
    createTestComplaint();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestResponses();
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
  });

  it('should return an error if user does not have permission', function () {
    $complaint = getTestComplaint();
    $role = getTestRole('user');

    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->postJson("/api/complaints/{$complaint->id}/responses", [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if input data is invalid', function () {
    $complaint = getTestComplaint();
    $result = $this->postJson("/api/complaints/{$complaint->id}/responses", [
      'message' => '',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.message'))->toBeArray();
  });

  it('should create a response if input data is valid', function () {
    $complaint = getTestComplaint();
    $result = $this->postJson("/api/complaints/{$complaint->id}/responses", [
      'message' => 'test',
      'status' => 'pending'
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(201);
    expect($result->json('message'))->toBe('Response created successfully');
  });
});

describe('PATCH /api/complaints/{complaint}/responses/{response}', function () {
  beforeEach(function () {
    createTestUser();
    createTestCategory();
    createTestComplaint();
    createTestResponse();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestResponses();
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
  });

  it('should return an error if response is not found', function () {
    $complaint = getTestComplaint();
    $result = $this->patchJson("/api/complaints/{$complaint->id}/responses/" . test()->validUUID, [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Response not found');
  });

  it('should update response if input data is valid', function () {
    $response = getTestResponse();
    $result = $this->patchJson("/api/complaints/{$response->complaint_id}/responses/{$response->id}", [
      'message' => 'test1',
      'status' => 'pending'
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Response updated successfully');
    expect($result->json('data.message'))->toBe('test1');
  });
});

describe('DELETE /api/complaints/{complaint}/responses/{response}', function () {
  beforeEach(function () {
    createTestUser();
    createTestCategory();
    createTestComplaint();
    createTestResponse();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestResponses();
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
  });

  it('should return an error if response is not found', function () {
    $complaint = getTestComplaint();
    $result = $this->deleteJson("/api/complaints/{$complaint->id}/responses/" . test()->validUUID, [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Response not found');
  });

  it('should delete response if response id is valid', function () {
    $response = getTestResponse();
    $result = $this->deleteJson("/api/complaints/{$response->complaint_id}/responses/{$response->id}", [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Response deleted successfully');
  });
});

