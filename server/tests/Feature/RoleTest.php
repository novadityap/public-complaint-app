<?php

describe('GET /api/roles', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestUsers();
    removeAllTestRoles();
  });

  it('should return an error if user does not have permission', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->getJson('/api/roles', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return all roles', function () {
    createManyTestRoles();

    $result = $this->getJson('/api/roles', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Roles retrieved successfully');
    expect($result->json('data'))->not()->toBeNull();
  });
});

describe('GET /api/roles/search', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
    createManyTestRoles();
  });

  afterEach(function () {
    removeAllTestUsers();
    removeAllTestRoles();
  });

  it('should return an error if user does not have permission', function () {
    $role = getTestRole('user');

    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->getJson('/api/roles/search', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return a list of roles with default pagination', function () {
    $result = $this->getJson('/api/roles/search', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Roles retrieved successfully');
    expect($result->json('data'))->toHaveCount(10);
    expect($result->json('meta.pageSize'))->toBe(10);
    expect($result->json('meta.totalItems'))->toBeGreaterThanOrEqual(15);
    expect($result->json('meta.currentPage'))->toBe(1);
    expect($result->json('meta.totalPages'))->toBe(2);
  });

  it('should return a list of roles with custom pagination', function () {
    $result = $this->getJson('/api/roles/search?page=2', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Roles retrieved successfully');
    expect(count($result->json('data')))->toBeGreaterThanOrEqual(5);
    expect($result->json('meta.pageSize'))->toBe(10);
    expect($result->json('meta.totalItems'))->toBeGreaterThanOrEqual(15);
    expect($result->json('meta.currentPage'))->toBe(2);
    expect($result->json('meta.totalPages'))->toBe(2);
  });

  it('should return a list of roles with custom search', function () {
    $result = $this->getJson('/api/roles/search?q=test10', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Roles retrieved successfully');
    expect($result->json('data'))->toHaveCount(1);
    expect($result->json('meta.pageSize'))->toBe(10);
    expect($result->json('meta.totalItems'))->toBe(1);
    expect($result->json('meta.currentPage'))->toBe(1);
    expect($result->json('meta.totalPages'))->toBe(1);
  });
});

describe('GET /api/roles/{roleId}', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestUsers();
    removeAllTestRoles();
  });

  it('should return an error if user does not have permission', function () {
    $role = getTestRole('user');
    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->getJson("/api/roles/{$role->id}", [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if role is not found', function () {
    $result = $this->getJson('/api/roles/' . test()->validUUID, [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Role not found');
  });

  it('should return a role if role id is valid', function () {
    createTestRole();
    $role = getTestRole();

    $result = $this->getJson("/api/roles/{$role->id}", [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Role retrieved successfully');
    expect($result->json('data'))->not()->toBeNull();
  });
});

describe('POST /api/roles', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestUsers();
    removeAllTestRoles();
  });

  it('should return an error if user does not have permission', function () {
    $role = getTestRole('user');

    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->postJson('/api/roles', [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if input data is invalid', function () {
    $result = $this->postJson('/api/roles', [
      'name' => '',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.name'))->toBeArray();
  });

  it('should return an error if name already in use', function () {
    createTestRole();

    $result = $this->postJson('/api/roles', [
      'name' => 'test',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.name'))->toBeArray();
  });

  it('should create a role if input data is valid', function () {
    $result = $this->postJson('/api/roles', [
      'name' => 'test',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(201);
    expect($result->json('message'))->toBe('Role created successfully');
  });
});

describe('PATCH /api/roles/{roleId}', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
    createTestRole();
  });

  afterEach(function () {
    removeAllTestUsers();
    removeAllTestRoles();
  });

  it('should return an error if user does not have permission', function () {
    $role = getTestRole('user');

    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->patchJson("/api/roles/{$role->id}", [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if name already in use', function () {
    createTestRole(['name' => 'test1']);

    $role = getTestRole();
    $result = $this->patchJson("/api/roles/{$role->id}", [
      'name' => 'test1',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.name'))->toBeArray();
  });

  it('should return an error if role is not found', function () {
    $result = $this->patchJson('/api/roles/' . test()->validUUID, [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Role not found');
  });

  it('should update role if input data is valid', function () {
    $role = getTestRole();

    $result = $this->patchJson("/api/roles/{$role->id}", [
      'name' => 'test1',
    ], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Role updated successfully');
    expect($result->json('data.name'))->toBe('test1');
  });
});

describe('DELETE /api/roles/{roleId}', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
    createTestRole();
  });

  afterEach(function () {
    removeAllTestUsers();
    removeAllTestRoles();
  });

  it('should return an error if user does not have permission', function () {
    $role = getTestRole();
    $userRole = getTestRole('user');

    updateTestUser(['role_id' => $userRole->id]);
    createAccessToken();

    $result = $this->deleteJson("/api/roles/{$role->id}", [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if role is not found', function () {
    $result = $this->deleteJson('/api/roles/' . test()->validUUID, [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('Role not found');
  });

  it('should delete role if role id is valid', function () {
    $role = getTestRole();

    $result = $this->deleteJson("/api/roles/{$role->id}", [], [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Role deleted successfully');
  });
});


