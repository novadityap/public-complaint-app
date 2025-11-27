<?php

namespace App\Helpers;

class CloudinaryHelper
{
  public static function extractPublicId(string $url): ?string
  {
    if (!str_contains($url, 'cloudinary.com')) {
      return null;
    }

    $path = parse_url($url, PHP_URL_PATH);
    $clean = preg_replace('#^/[^/]+/[^/]+/upload/v\d+/#', '', $path);
    $clean = ltrim($clean, '/');
    $clean = explode('?', $clean)[0];

    return $clean ?: null;
  }
}
