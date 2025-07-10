<?php

describe('GET /api/categories', function () {
  it('should return all categories', function () {
    createTestUser();
    createAccessToken();
    createManyTestCategories();

    $result = $this->getJson('/api/categories', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Categories retrieved successfully');

    removeAllTestCategories();
    removeAllTestUsers();
  });
});

describe('GET /api/categories/search', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
    createManyTestCategories();
  });

  afterEach(function () {
    removeAllTestCategories();
    removeAllTestUsers();
  });

  it('should return a list of categories with default pagination', function () {
    $result = $this->getJson('/api/categories/search', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Categories retrieved successfully');
    expect(count($result->json('data')))->toBe(10);
    expect($result->json('meta.pageSize'))->toBe(10);
    expect($result->json('meta.totalItems'))->toBeGreaterThanOrEqual(25);
    expect($result->json('meta.currentPage'))->toBe(1);
    expect($result->json('meta.totalPages'))->toBeGreaterThanOrEqual(2);
  });

  it('should return a list of categories with custom search', function () {
    $result = $this->getJson('/api/categories/search?q=test10', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Categories retrieved successfully');
    expect(count($result->json('data')))->toBe(1);
    expect($result->json('meta.pageSize'))->toBe(10);
    expect($result->json('meta.totalItems'))->toBe(1);
    expect($result->json('meta.currentPage'))->toBe(1);
    expect($result->json('meta.totalPages'))->toBe(1);
  });
});

describe('GET /api/categories/{category}', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestCategories();
    removeAllTestUsers();
  });

  it('should return an error if category is not found', function () {
    $result = $this->getJson('/api/categories/' . test()->validUUID, [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Category not found');
  });

  it('should return a category if category id is valid', function () {
    $category = createTestCategory();

    $result = $this->getJson("/api/categories/{$category->id}", [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Category retrieved successfully');
    expect($result->json('data'))->toBeArray();
  });
});

describe('POST /api/categories', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestCategories();
    removeAllTestUsers();
  });

  it('should return an error if user does not have permission', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->postJson('/api/categories', [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if input data is invalid', function () {
    $result = $this->postJson('/api/categories', [
      'name' => '',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.name'))->toBeArray();
  });

  it('should return an error if name already in use', function () {
    createTestCategory();

    $result = $this->postJson('/api/categories', [
      'name' => 'test',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.name'))->toBeArray();
  });

  it('should create a category if input data is valid', function () {
    $result = $this->postJson('/api/categories', [
      'name' => 'test',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(201);
    expect($result->json('message'))->toBe('Category created successfully');
  });
});

describe('PATCH /api/categories/{category}', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
    createTestCategory();
  });

  afterEach(function () {
    removeAllTestCategories();
    removeAllTestUsers();
  });

  it('should return an error if user does not have permission', function () {
    $userRole = getTestRole('user');
    updateTestUser(['role_id' => $userRole->id]);
    createAccessToken();

    $category = getTestCategory();

    $result = $this->patchJson("/api/categories/{$category->id}", [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if name already in use', function () {
    createTestCategory(['name' => 'test1']);

    $category = getTestCategory();
    $result = $this->patchJson("/api/categories/{$category->id}", [
      'name' => 'test1',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.name'))->toBeArray();
  });

  it('should return an error if category is not found', function () {
    $result = $this->patchJson('/api/categories/' . test()->validUUID, [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Category not found');
  });

  it('should update category if input data is valid', function () {
    $category = getTestCategory();
    $result = $this->patchJson("/api/categories/{$category->id}", [
      'name' => 'test1',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Category updated successfully');
    expect($result->json('data.name'))->toBe('test1');
  });
});

describe('DELETE /api/categories/{category}', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
    createTestCategory();
  });

  afterEach(function () {
    removeAllTestCategories();
    removeAllTestUsers();
  });

  it('should return an error if user does not have permission', function () {
    $userRole = getTestRole('user');
    updateTestUser(['role_id' => $userRole->id]);
    createAccessToken();

    $category = getTestCategory();

    $result = $this->deleteJson("/api/categories/{$category->id}", [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if category is not found', function () {
    $result = $this->deleteJson('/api/categories/' . test()->validUUID, [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Category not found');
  });

  it('should delete category if category id is valid', function () {
    $category = getTestCategory();

    $result = $this->deleteJson("/api/categories/{$category->id}", [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Category deleted successfully');
  });
});

