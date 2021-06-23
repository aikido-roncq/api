<?php

namespace App\Controllers;

use App\Attributes\Route;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use App\Models\Gallery;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Utils\Http;

#[Route('/gallery')]
class GalleryController extends Controller
{
  private const ALLOWED_EXT = [
    'jpeg' => 'imagejpeg',
    'png' => 'imagepng',
    'webp' => 'imagewebp',
    'gif' => 'imagegif'
  ];

  private const BASE_URL = 'assets/gallery';

  #[Route('[/]', 'GET')]
  public function all(Request $req, Response $res)
  {
    $gallery = Gallery::orderBy('added');
    return self::send($res, $gallery);
  }

  #[Route('[/]', 'POST', admin: true)]
  public function add(Request $req, Response $res)
  {
    if (!array_key_exists('image', $_FILES)) {
      throw new ValidationException('image not received', ['image' => 'Image non reçue']);
    }

    $post = $req->getParsedBody();
    $src = self::upload($_FILES['image']);

    $data = [
      'caption' => $post['caption'] ?? null,
      'src' => $src
    ];

    $image = Gallery::insert($data);

    return self::send($res, $image, Http::CREATED);
  }

  #[Route('/{id}', 'DELETE', admin: true)]
  public function delete(Request $req, Response $res, array $params)
  {
    $deleted = Gallery::delete($params['id']);
    unlink(ROOT . '/public/' . $deleted->src);
    return self::send($res, $deleted);
  }

  private static function upload(array $file): string
  {
    $filename = hash('crc32', microtime());
    [$type, $ext] = explode('/', $file['type']);

    if ($type != 'image') {
      throw new ValidationException("$type is not valid image type", [
        'image' => 'Type de fichier invalide.'
      ]);
    } elseif (!in_array($ext, array_keys(self::ALLOWED_EXT))) {
      throw new ValidationException("$ext not supported", [
        'image' => 'Extension non supportée.'
      ]);
    }

    $publicPath = self::BASE_URL . "/$filename.$ext";
    $realPath = ROOT . "/public/$publicPath";

    if (!self::resizeImage($file['tmp_name'], $ext)) {
      throw new UnknownException('could not resize image');
    }

    if (!move_uploaded_file($file['tmp_name'], $realPath)) {
      throw new UnknownException('could not move uploaded image');
    }

    return $publicPath;
  }

  private static function resizeImage(string $path, string $ext): bool
  {
    $image = imagecreatefromstring(file_get_contents($path));
    [$initWidth, $initHeight] = getimagesize($path);

    if (!$image) {
      throw new UnknownException('could not create image from string');
    }

    [$width, $height] = $initWidth > $initHeight
      ? [$initHeight * (16 / 9), $initHeight]
      : [$initWidth, $initWidth / (16 / 9)];

    [$x, $y] = [($initWidth - $width) / 2, ($initHeight - $height) / 2];

    $resized = imagecrop($image, compact('x', 'y', 'width', 'height'));

    if (!$resized) {
      throw new UnknownException('could not crop image');
    }

    return call_user_func_array(self::ALLOWED_EXT[$ext], [$resized, $path]);
  }
}
