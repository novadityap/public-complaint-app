<?php

namespace App\Helpers;

class CloudinaryHelper
{
  public static function extractPublicId(string $url): ?string
  {
    if (strpos($url, 'cloudinary.com') === false) return null;

    $parts = explode('/', $url);
    $uploadIndex = array_search('upload', $parts);

    if ($uploadIndex === false || !isset($parts[$uploadIndex + 2])) return null;

    $publicIdParts = array_slice($parts, $uploadIndex + 2);
    $joined = implode('/', $publicIdParts);
    $withoutExtension = preg_replace('/\.[^.\s]{3,4}$/', '', $joined);

    return $withoutExtension;
  }
}
