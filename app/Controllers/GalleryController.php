<?php

namespace App\Controllers;

use App\Attributes\Route;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use App\Models\Gallery;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

#[Route('/gallery')]
class GalleryController extends Controller
{
  private const ALLOWED_EXT = [
    'jpeg' => 'imagejpeg',
    'png' => 'imagepng',
    'webp' => 'imagewebp',
    'gif' => 'imagegif'
  ];

  #[Route('[/]', 'GET')]
  public function all(Request $req, Response $res)
  {
    $gallery = Gallery::orderBy('added');
    return self::send($res, $gallery);
  }

  #[Route('[/]', 'POST', admin: true)]
  public function add(Request $req, Response $res)
  {
    if (!array_key_exists('image', $_FILES))
      return self::badRequest($res, ['image' => 'Image non reçue.']);

    $post = $req->getParsedBody() ?? [];
    $data = ['caption' => $post['caption'] ?? null];

    try {
      $src = self::upload($_FILES['image']);
      $image = Gallery::create(array_merge($data, compact('src')));
    } catch (ValidationException $e) {
      return self::badRequest($res, $e->getErrors());
    }

    return self::send($res, $image, 201);
  }

  private static function upload(array $file): string
  {
    $filename = bin2hex(random_bytes(4));
    [$type, $ext] = explode('/', $file['type']);

    if ($type != 'image')
      throw new ValidationException(['image' => 'Type de fichier invalide.']);
    elseif (!in_array($ext, array_keys(self::ALLOWED_EXT)))
      throw new ValidationException(['image' => 'Extension non supportée.']);

    $publicPath = sprintf('/assets/gallery/%s.%s', $filename, $ext);
    $realPath = ROOT . '/public' . $publicPath;

    if (!self::resizeImage($file['tmp_name'], $ext))
      throw new UnknownException();

    if (!move_uploaded_file($file['tmp_name'], $realPath))
      throw new UnknownException();

    return $publicPath;
  }

  private static function resizeImage(string $path, string $ext): bool
  {
    $image = imagecreatefromstring(file_get_contents($path));
    [$initWidth, $initHeight] = getimagesize($path);

    [$width, $height] = $initWidth > $initHeight
      ? [$initHeight * (16 / 9), $initHeight]
      : [$initWidth, $initWidth / (16 / 9)];

    [$x, $y] = [($initWidth - $width) / 2, ($initHeight - $height) / 2];

    $resized = imagecrop($image, compact('x', 'y', 'width', 'height'));

    if (!$resized)
      throw new UnknownException();

    return call_user_func_array(self::ALLOWED_EXT[$ext], [$resized, $path]);
  }
}
