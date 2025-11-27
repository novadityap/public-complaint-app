<?php

use Illuminate\Http\UploadedFile;

describe('GET /api/complaints/search', function () {
  beforeEach(function () {
    createTestCategory();
    createTestUser();
    createManyTestComplaints();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
  });

  it('should return a list of complaints with default pagination', function () {
    $result = $this->getJson('/api/complaints/search', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Complaints retrieved successfully');
    expect($result->json('data'))->toHaveCount(10);
    expect($result->json('meta.pageSize'))->toBe(10);
    expect($result->json('meta.totalItems'))->toBeGreaterThanOrEqual(15);
    expect($result->json('meta.currentPage'))->toBe(1);
    expect($result->json('meta.totalPages'))->toBeGreaterThanOrEqual(2);
  });

  it('should return a list of complaints with custom search', function () {
    $result = $this->getJson('/api/complaints/search?q=test10', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Complaints retrieved successfully');
    expect($result->json('data'))->toHaveCount(1);
    expect($result->json('meta.pageSize'))->toBe(10);
    expect($result->json('meta.totalItems'))->toBe(1);
    expect($result->json('meta.currentPage'))->toBe(1);
    expect($result->json('meta.totalPages'))->toBe(1);
  });
});

describe('GET /api/complaints/{complaintId}', function () {
  beforeEach(function () {
    createTestCategory();
    createTestUser();
    createTestComplaint();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
  });

  it('should return an error if complaint is not owned by current user', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);

    $otherUser = createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
      'role_id' => $role->id,
    ]);

    $updatedComplaint = updateTestComplaint([
      'user_id' => $otherUser->id,
    ]);

    $result = $this->getJson("/api/complaints/{$updatedComplaint->id}", [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if complaint is not found', function () {
    $result = $this->getJson('/api/complaints/' . test()->validUUID, [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Complaint not found');
  });

  it('should return a complaint for complaint id is valid', function () {
    $complaint = getTestComplaint();

    $result = $this->getJson("/api/complaints/{$complaint->id}", [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Complaint retrieved successfully');
    expect($result->json('data'))->not()->toBeNull();
  });
});

describe('POST /api/complaints', function () {
  beforeEach(function () {
    createTestUser();
    createTestCategory();
    createAccessToken();

    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);
  });

  afterEach(function () {
    removeAllTestUsers();
    removeAllTestCategories();
  });

  it('should return an error if input data is invalid', function () {
    $result = $this->post('/api/complaints', [
      'subject' => '',
      'description' => '',
      'categoryId' => '',
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
      'Content-Type' => 'multipart/form-data'
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.subject'))->toBeArray();
    expect($result->json('errors.description'))->toBeArray();
    expect($result->json('errors.categoryId'))->toBeArray();
  });

  it('should return an error if category is invalid', function () {
    $result = $this->post('/api/complaints', [
      'subject' => 'test',
      'description' => 'test',
      'categoryId' => 'invalid-id',
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
      'Content-Type' => 'multipart/form-data'
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.categoryId'))->toBeArray();
  });

  it('should create a complaint with image if input data is valid', function () {
    $category = getTestCategory();

    $result = $this->post('/api/complaints', [
      'subject' => 'test',
      'description' => 'test',
      'categoryId' => $category->id,
      'images' => [
        new UploadedFile(
          test()->testComplaintImagePath,
          'test-complaint.jpg',
          null,
          null,
          true
        )
      ],
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
      'Content-Type' => 'multipart/form-data'
    ]);

    $complaint = getTestComplaint();
    $imagesExist = checkFileExists($complaint->images);

    expect($result->status())->toBe(201);
    expect($result->json('message'))->toBe('Complaint created successfully');
    expect($imagesExist)->toBeTrue();

    removeTestFile($complaint->images);
  });
});

describe('PATCH /api/complaints/{complaintId}', function () {
  beforeEach(function () {
    createTestCategory();
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
  });

  it('should return an error if complaint is not owned by current user', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);

    $otherUser = createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
      'role_id' => $role->id,
    ]);

    $complaint = createTestComplaint(['user_id' => $otherUser->id]);

    $result = $this->patch("/api/complaints/{$complaint->id}", [], [
      'Authorization' => "Bearer {$this->accessToken}",
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if complaint is not found', function () {
    $result = $this->patch("/api/complaints/{$this->validUUID}", [], [
      'Authorization' => "Bearer {$this->accessToken}",
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Complaint not found');
  });

  it('should return an error if input data is invalid', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);

    $complaint = createTestComplaint();

    $result = $this->patch("/api/complaints/{$complaint->id}", [
      'subject' => '',
      'description' => '',
      'categoryId' => '',
    ], [
      'Authorization' => "Bearer {$this->accessToken}",
      'Content-Type' => 'multipart/form-data',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.subject'))->toBeArray();
    expect($result->json('errors.description'))->toBeArray();
    expect($result->json('errors.categoryId'))->toBeArray();
  });

  it('should return an error if category is invalid', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);

    $complaint = createTestComplaint();

    $result = $this->patch("/api/complaints/{$complaint->id}", [
      'subject' => 'test',
      'description' => 'test',
      'categoryId' => 'invalid-id',
    ], [
      'Authorization' => "Bearer {$this->accessToken}",
      'Content-Type' => 'multipart/form-data'
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.categoryId'))->toBeArray();
  });

  it('should update complaint with changing images', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);

    $category = getTestCategory();
    $complaint = createTestComplaint();

    $result = $this
      ->patch("/api/complaints/{$complaint->id}", [
        'subject' => 'test1',
        'description' => 'test1',
        'categoryId' => $category->id,
        'images' => [
          new UploadedFile(
            test()->testComplaintImagePath,
            'test-complaint.jpg',
            null,
            null,
            true
          )
        ],
      ], [
        'Authorization' => "Bearer {$this->accessToken}",
        'Content-Type' => 'multipart/form-data'
      ]);

    $updatedComplaint = getTestComplaint('test1');
    $imagesExist = checkFileExists($updatedComplaint->images);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Complaint updated successfully');
    expect($result->json('data.subject'))->toBe('test1');
    expect($result->json('data.description'))->toBe('test1');
    expect($imagesExist)->toBeTrue();

    removeTestFile($updatedComplaint->images);
  });
});

describe('DELETE /api/complaints/{complaintId}', function () {
  beforeEach(function () {
    createTestUser();
    createTestCategory();
    createTestComplaint();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
  });

  it('should return an error if user is not owned by current user', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);

    $otherUser = createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
      'role_id' => $role->id,
    ]);

    $updatedComplaint = updateTestComplaint(['user_id' => $otherUser->id]);

    $result = $this->deleteJson("/api/complaints/{$updatedComplaint->id}", [], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if complaint is not found', function () {
    $result = $this->deleteJson("/api/complaints/" . test()->validUUID, [], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Complaint not found');
  });

  it('should delete complaint with removing image', function () {
    $publicId = Storage::putFile('complaints', test()->testComplaintImagePath);
    $updatedComplaint = updateTestComplaint(['images' => [Storage::url($publicId)]]);

    $result = $this->deleteJson("/api/complaints/{$updatedComplaint->id}", [], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    $imageExists = checkFileExists($updatedComplaint->images);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Complaint deleted successfully');
    expect($imageExists)->toBeFalse();
  });
});

describe('POST /api/complaints/{complaintId}/images', function () {
  it('should upload complaint images', function () {
    createTestCategory();
    createTestUser();
    createTestComplaint();
    createAccessToken();

    $complaint = getTestComplaint();
    $result = $this
      ->post("/api/complaints/{$complaint->id}/images", [
        'images' => [
          new UploadedFile(
            test()->testComplaintImagePath,
            'test-complaint.jpg',
            null,
            null,
            true
          )
        ],
      ], [
        'Authorization' => "Bearer {$this->accessToken}",
        'Content-Type' => 'multipart/form-data'
      ]);

    expect($result->status())->toBe(201);
    expect($result->json('message'))->toBe('Complaint images uploaded successfully');

    $updatedComplaint = getTestComplaint();

    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
    removeTestFile($updatedComplaint->images);
  });
});

describe('DELETE /api/complaints/{complaintId}/images', function () {
  beforeEach(function () {
    createTestCategory();
    createTestUser();
    createTestComplaint();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestComplaints();
    removeAllTestUsers();
    removeAllTestCategories();
  });

  it('should return an error if complaint is not owned by current user', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);

    $otherUser = createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
      'role_id' => $role->id,
    ]);

    $publicId = Storage::putFile('complaints', test()->testComplaintImagePath);
    $updatedComplaint = updateTestComplaint([
      'user_id' => $otherUser->id,
      'images' => [Storage::url($publicId)],
    ]);

    createAccessToken();

    $result = $this->deleteJson(
      "/api/complaints/{$updatedComplaint->id}/images",
      ['image' => $updatedComplaint->images[0]],
      ["Authorization" => "Bearer " . test()->accessToken]
    );

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');

    removeTestFile($updatedComplaint->images);
  });

  it('should return an error if complaint is not found', function () {
    $result = $this->deleteJson(
      "/api/complaints/" . test()->validUUID . "/images",
      [],
      ["Authorization" => "Bearer " . test()->accessToken]
    );

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Complaint not found');
  });

  it('should delete complaint images', function () {
    $publicId = Storage::putFile('complaints', test()->testComplaintImagePath);
    $updatedComplaint = updateTestComplaint(['images' => [Storage::url($publicId)]]);

    $result = $this->deleteJson(
      "/api/complaints/{$updatedComplaint->id}/images",
      ['image' => $updatedComplaint->images[0]],
      ["Authorization" => "Bearer " . test()->accessToken]
    );

    $imageExist = checkFileExists($updatedComplaint->images);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Complaint image deleted successfully');
    expect($imageExist)->toBeFalse();
  });
});