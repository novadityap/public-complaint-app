<?php

use App\Models\Role;
use App\Models\User;
use Firebase\JWT\JWT;
use App\Models\Category;
use App\Models\Response;
use App\Models\Complaint;
use App\Models\RefreshToken;
use Illuminate\Support\Carbon;
use App\Helpers\CloudinaryHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
  ->beforeAll(function () {
    Artisan::call('migrate:refresh --seed');
  })
  ->beforeEach(function () {
    test()->validUUID = Str::uuid()->toString();
    test()->testAvatarPath = base_path('tests/uploads/avatars/test-avatar.jpg');
    test()->testComplaintImagePath = base_path('tests/uploads/complaints/test-complaint.jpg');
  })
  ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
  return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function getTestRefreshToken(): ?RefreshToken
{
  $user = getTestUser();

  return RefreshToken::where('user_id', $user->id)->first();
}

function createTestRefreshToken(): RefreshToken
{
  $user = getTestUser();
  $token = JWT::encode(
    [
      'sub' => $user->id,
      'role' => $user->role->name,
      'iat' => now()->timestamp,
      'exp' => now()->addMinutes((int) config('auth.jwt_refresh_expires'))->timestamp
    ],
    config('auth.jwt_refresh_secret'),
    config('auth.jwt_algo')
  );

  return RefreshToken::create([
    'token' => $token,
    'user_id' => $user->id,
    'expires_at' => Carbon::now()->addMinutes(5),
  ]);
}

function removeAllTestRefreshTokens(): void
{
  $testUsers = User::where('username', 'ilike', 'test%')->pluck('id');

  RefreshToken::whereIn('user_id', $testUsers)->delete();
}

function getTestUser(string $username = 'test'): ?User
{
  return User::with('role')->where('username', $username)->first();
}

function createTestUser(array $fields = []): User
{
  $role = getTestRole('admin');

  return User::create(array_merge([
    'username' => 'test',
    'email' => 'test@me.com',
    'password' => Hash::make('test123'),
    'role_id' => $role->id,
  ], $fields));
}

function createManyTestUsers(): void
{
  $role = getTestRole('admin');

  foreach (range(0, 14) as $i) {
    User::create([
      'username' => "test{$i}",
      'email' => "test{$i}@email.com",
      'password' => Hash::make('test123'),
      'role_id' => $role->id,
      'avatar' => config('app.default_avatar_url'),
    ]);
  }
}

function updateTestUser(array $fields = []): ?User
{
  $user = getTestUser();
  $user->update($fields);
  return $user->fresh();
}

function removeAllTestUsers(): void
{
  User::where('username', 'ilike', 'test%')->delete();
}

function getTestRole(string $name = 'test'): ?Role
{
  return Role::where('name', $name)->first();
}

function createTestRole(array $fields = []): Role
{
  return Role::create(array_merge(['name' => 'test'], $fields));
}

function createManyTestRoles(): void
{
  foreach (range(0, 14) as $i) {
    Role::create(['name' => "test{$i}"]);
  }
}

function removeAllTestRoles(): void
{
  Role::where('name', 'ilike', 'test%')->delete();
}

function getTestCategory(string $name = 'test'): ?Category
{
  return Category::where('name', $name)->first();
}

function createTestCategory(array $fields = []): Category
{
  return Category::create(array_merge(['name' => 'test'], $fields));
}

function createManyTestCategories(): void
{
  foreach (range(0, 14) as $i) {
    Category::create(['name' => "test{$i}"]);
  }
}

function removeAllTestCategories(): void
{
  Category::where('name', 'ilike', 'test%')->delete();
}


function getTestComplaint(string $subject = 'test'): ?Complaint
{
  return Complaint::where('subject', $subject)->first();
}

function createTestComplaint(array $fields = []): Complaint
{
  $user = getTestUser();
  $category = getTestCategory();

  return Complaint::create(array_merge([
    'subject' => 'test',
    'description' => 'test',
    'user_id' => $user->id,
    'category_id' => $category->id,
  ], $fields));
}

function createManyTestComplaints(): void
{
  $user = getTestUser();
  $category = getTestCategory();

  foreach (range(0, 14) as $i) {
    Complaint::create([
      'subject' => "test{$i}",
      'description' => "test{$i}",
      'user_id' => $user->id,
      'category_id' => $category->id,
    ]);
  }
}

function updateTestComplaint(array $fields = []): ?Complaint
{
  $complaint = getTestComplaint();
  $complaint->update($fields);
  return $complaint->fresh();
}

function removeAllTestComplaints(): void
{
  Complaint::where('subject', 'ilike', 'test%')->delete();
}

function getTestResponse(string $message = 'test'): ?Response
{
  return Response::where('message', $message)->first();
}

function createTestResponse(array $fields = []): Response
{
  $user = getTestUser();
  $complaint = getTestComplaint();

  return Response::create(array_merge([
    'message' => 'test',
    'user_id' => $user->id,
    'complaint_id' => $complaint->id,
  ], $fields));
}

function createManyTestResponses(): void
{
  $user = getTestUser();
  $complaint = getTestComplaint();

  foreach (range(0, 14) as $i) {
    Response::create([
      'message' => 'test',
      'user_id' => $user->id,
      'complaint_id' => $complaint->id,
    ]);
  }
}

function updateTestResponse(array $fields = []): ?Response
{
  $response = getTestResponse();
  $response->update($fields);
  return $response->fresh();
}

function removeAllTestResponses(): void
{
  Response::where('message', 'ilike', 'test%')->delete();
}

function createAccessToken(): void
{
  $user = getTestUser();
  $token = JWT::encode(
    [
      'sub' => $user->id,
      'role' => $user->role->name,
      'iat' => now()->timestamp,
      'exp' => now()->addMinutes((int) config('auth.jwt_expires'))->timestamp
    ],
    config('auth.jwt_secret'),
    config('auth.jwt_algo')
  );

  test()->accessToken = $token;
}

function checkFileExists(string|array $url): bool
{
  try {
    $urls = is_array($url) ? $url : [$url];

    foreach ($urls as $item) {
      cloudinary()->adminApi()->asset(CloudinaryHelper::extractPublicId($item));
    }
    return true;
  } catch (\Exception $e) {
    return false;
  }
}

function removeTestFile(string|array $url): void
{
  $urls = is_array($url) ? $url : [$url];

  foreach ($urls as $item) {
    cloudinary()->uploadApi()->destroy(CloudinaryHelper::extractPublicId($item));
  }
}