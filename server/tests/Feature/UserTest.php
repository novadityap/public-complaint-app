<?php

use Illuminate\Http\UploadedFile;

describe('GET /api/users/search', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
    createManyTestUsers();
  });

  afterEach(function () {
    removeAllTestUsers();
  });

  it('should return an error if user does not have permission', function () {
    $role = getTestRole('user');

    updateTestUser([
      'role_id' => $role->id,
    ]);
    createAccessToken();

    $result = $this->getJson('/api/users/search', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return a list of users with default pagination', function () {
    $result = $this->getJson('/api/users/search', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Users retrieved successfully');
    expect($result->json('data'))->toHaveCount(10);
    expect($result->json('meta.pageSize'))->toBe(10);
    expect($result->json('meta.totalItems'))->toBeGreaterThanOrEqual(15);
    expect($result->json('meta.currentPage'))->toBe(1);
    expect($result->json('meta.totalPages'))->toBeGreaterThanOrEqual(2);
  });

  it('should return a list of users with custom search', function () {
    $result = $this->getJson('/api/users/search?q=test10', [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Users retrieved successfully');
    expect($result->json('data'))->toHaveCount(1);
    expect($result->json('meta.pageSize'))->toBe(10);
    expect($result->json('meta.totalItems'))->toBe(1);
    expect($result->json('meta.currentPage'))->toBe(1);
    expect($result->json('meta.totalPages'))->toBe(1);
  });
});

describe('GET /api/users/{userId}', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestUsers();
  });

  it('should return an error if user is not owned by current user', function () {
    $role = getTestRole('user');

    $otherUser = createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
      'role_id' => $role->id,
    ]);

    updateTestUser([
      'role_id' => $role->id,
    ]);
    createAccessToken();

    $result = $this->getJson("/api/users/{$otherUser->id}", [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if user is not found', function () {
    $result = $this->getJson('/api/users/' . test()->validUUID, [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('User not found');
  });

  it('should return a user for user id is valid', function () {
    $user = getTestUser();

    $result = $this->getJson("/api/users/{$user->id}", [
      'Authorization' => 'Bearer ' . test()->accessToken,
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('User retrieved successfully');
    expect($result->json('data'))->not()->toBeNull();
  });
});

describe('POST /api/users', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestUsers();
  });

  it('should return an error if user does not have permission', function () {
    $role = getTestRole('user');

    updateTestUser([
      'role_id' => $role->id,
    ]);
    createAccessToken();

    $result = $this->postJson('/api/users', [
      'username' => 'test1',
      'email' => 'test1@me.com',
      'password' => 'password',
      'roleId' => $role->id,
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if input data is invalid', function () {
    $result = $this->postJson('/api/users', [
      'username' => '',
      'email' => '',
      'password' => '',
      'roleId' => '',
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.username'))->toBeArray();
    expect($result->json('errors.email'))->toBeArray();
    expect($result->json('errors.password'))->toBeArray();
    expect($result->json('errors.roleId'))->toBeArray();
  });

  it('should return an error if email already in use', function () {
    createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
    ]);

    $role = getTestRole('admin');

    $result = $this->postJson('/api/users', [
      'username' => 'test1',
      'email' => 'test@me.com',
      'password' => 'test123',
      'roleId' => $role->id,
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.email'))->toBeArray();
  });

  it('should return an error if username already in use', function () {
    createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
    ]);

    $role = getTestRole('admin');

    $result = $this->postJson('/api/users', [
      'username' => 'test',
      'email' => 'test1@me.com',
      'password' => 'test123',
      'roleId' => $role->id,
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.username'))->toBeArray();
  });

  it('should return an error if role is invalid', function () {
    $result = $this->postJson('/api/users', [
      'username' => 'test',
      'email' => 'test@me.com',
      'password' => 'test123',
      'roleId' => 'invalid-id',
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.roleId'))->toBeArray();
  });

  it('should create a user if input data is valid', function () {
    $role = getTestRole('admin');

    $result = $this->postJson('/api/users', [
      'username' => 'test1',
      'email' => 'test1@me.com',
      'password' => 'test123',
      'roleId' => $role->id,
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(201);
    expect($result->json('message'))->toBe('User created successfully');
  });
});

describe('PATCH /api/users/:userId/profile', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestUsers();
  });

  it('should return an error if user is not owned by current user', function () {
    $role = getTestRole('user');
    $otherUser = createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
      'role_id' => $role->id,
    ]);

    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->patchJson("/api/users/{$otherUser->id}/profile", [], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if user is not found', function () {
    $result = $this->patchJson("/api/users/" . test()->validUUID . "/profile", [], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('User not found');
  });

  it('should return an error if input data is invalid', function () {
    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}/profile", [
      'email' => '',
      'username' => '',
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
      'Content-Type' => 'multipart/form-data',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.username'))->toBeArray();
    expect($result->json('errors.email'))->toBeArray();
  });

  it('should return an error if email is already in use', function () {
    createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
    ]);

    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}/profile", [
      'email' => 'test1@me.com',
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
      'Content-Type' => 'multipart/form-data',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.email'))->toBeArray();
  });

  it('should return an error if username is already in use', function () {
    createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
    ]);

    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}/profile", [
      'username' => 'test1',
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
      'Content-Type' => 'multipart/form-data',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.username'))->toBeArray();
  });

  it('should update profile without changing avatar', function () {
    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}/profile", [
      'username' => 'test1',
      'email' => 'test1@me.com',
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
      'Content-Type' => 'multipart/form-data',
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Profile updated successfully');
    expect($result->json('data.email'))->toBe('test1@me.com');
    expect($result->json('data.username'))->toBe('test1');
  });

  it('should update profile with changing avatar', function () {
    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}/profile", [
      'username' => 'test1',
      'email' => 'test1@me.com',
      'avatar' => new UploadedFile(
        test()->testAvatarPath,
        'avatar.jpg',
        null,
        null,
        true
      ),
    ], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    $updatedUser = getTestUser('test1');
    $avatarExists = checkFileExists($updatedUser->avatar);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('Profile updated successfully');
    expect($result->json('data.email'))->toBe('test1@me.com');
    expect($result->json('data.username'))->toBe('test1');
    expect($avatarExists)->toBeTrue();

    removeTestFile($updatedUser->avatar);
  });
});

describe('PATCH /api/users/{userId}', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestUsers();
  });

  it('should return an error if user is not owned by current user', function () {
    $role = getTestRole('user');
    $otherUser = createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
      'role_id' => $role->id,
    ]);

    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->patch("/api/users/{$otherUser->id}", [], [
      'Authorization' => "Bearer {$this->accessToken}",
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if user is not found', function () {
    $result = $this->patch("/api/users/{$this->validUUID}", [], [
      'Authorization' => "Bearer {$this->accessToken}",
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('User not found');
  });

  it('should return an error if input data is invalid', function () {
    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}", [
      'email' => '',
      'username' => '',
    ], [
      'Authorization' => "Bearer {$this->accessToken}",
      'Content-Type' => 'multipart/form-data',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.username'))->toBeArray();
    expect($result->json('errors.email'))->toBeArray();
  });

  it('should return an error if role is invalid', function () {
    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}", [
      'email' => 'test1@me.com',
      'username' => 'test1',
      'roleId' => 'invalid-id',
    ], [
      'Authorization' => "Bearer {$this->accessToken}",
      'Content-Type' => 'multipart/form-data',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.roleId'))->toBeArray();
  });

  it('should return an error if email is already in use', function () {
    createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
    ]);

    $role = getTestRole('admin');
    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}", [
      'email' => 'test1@me.com',
      'roleId' => $role->id,
    ], [
      'Authorization' => "Bearer {$this->accessToken}",
      'Content-Type' => 'multipart/form-data',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.email'))->toBeArray();
  });

  it('should return an error if username is already in use', function () {
    createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
    ]);

    $role = getTestRole('admin');
    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}", [
      'username' => 'test1',
      'email' => 'test1@me.com',
      'roleId' => $role->id,
    ], [
      'Authorization' => "Bearer {$this->accessToken}",
      'Content-Type' => 'multipart/form-data',
    ]);

    expect($result->status())->toBe(400);
    expect($result->json('message'))->toBe('Validation errors');
    expect($result->json('errors.username'))->toBeArray();
  });

  it('should update user without changing avatar', function () {
    $role = getTestRole('admin');
    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}", [
      'email' => 'test1@me.com',
      'username' => 'test1',
      'roleId' => $role->id,
    ], [
      'Authorization' => "Bearer {$this->accessToken}",
      'Content-Type' => 'multipart/form-data',
    ]);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('User updated successfully');
    expect($result->json('data.email'))->toBe('test1@me.com');
    expect($result->json('data.username'))->toBe('test1');
    expect($result->json('data.roleId'))->toBe($role->id);
  });

  it('should update user with changing avatar', function () {
    $user = getTestUser();

    $result = $this->patch("/api/users/{$user->id}", [
      'email' => 'test1@me.com',
      'username' => 'test1',
      'avatar' => new UploadedFile(
        test()->testAvatarPath,
        'test-avatar.jpg',
        null,
        null,
        true
      ),
    ], [
      'Authorization' => "Bearer {$this->accessToken}",
    ]);

    $updatedUser = getTestUser('test1');
    $avatarExists = checkFileExists($updatedUser->avatar);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('User updated successfully');
    expect($result->json('data.email'))->toBe('test1@me.com');
    expect($result->json('data.username'))->toBe('test1');
    expect($avatarExists)->toBeTrue();

    removeTestFile($updatedUser->avatar);
  });
});

describe('DELETE /api/users/{userId}', function () {
  beforeEach(function () {
    createTestUser();
    createAccessToken();
  });

  afterEach(function () {
    removeAllTestUsers();
  });

  it('should return an error if user is not owned by current user', function () {
    $role = getTestRole('user');
    $otherUser = createTestUser([
      'username' => 'test1',
      'email' => 'test1@me.com',
      'role_id' => $role->id,
    ]);

    updateTestUser(['role_id' => $role->id]);
    createAccessToken();

    $result = $this->deleteJson("/api/users/{$otherUser->id}", [], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(403);
    expect($result->json('message'))->toBe('Permission denied');
  });

  it('should return an error if user is not found', function () {
    $result = $this->deleteJson("/api/users/" . test()->validUUID, [], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    expect($result->status())->toBe(404);
    expect($result->json('message'))->toBe('User not found');
  });

  it('should delete user without removing default avatar', function () {
    $user = getTestUser();

    $result = $this->deleteJson("/api/users/{$user->id}", [], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    $avatarExists = checkFileExists(env('DEFAULT_AVATAR_URL'));

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('User deleted successfully');
    expect($avatarExists)->toBeTrue();
  });

  it('should delete user with removing avatar', function () {
    $user = getTestUser();
    $uploadResult = cloudinary()->uploadApi()->upload(
      test()->testAvatarPath,
      [
        'folder' => 'avatars'
      ]
    );
    $updatedUser = updateTestUser(['avatar' => $uploadResult['secure_url']]);

    $result = $this->deleteJson("/api/users/{$user->id}", [], [
      'Authorization' => "Bearer " . test()->accessToken,
    ]);

    $avatarExists = checkFileExists($updatedUser->avatar);

    expect($result->status())->toBe(200);
    expect($result->json('message'))->toBe('User deleted successfully');
    expect($avatarExists)->toBeFalse();
  });
});
