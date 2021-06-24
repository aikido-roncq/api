<?php

namespace App\Controllers;

use App\Attributes\Route;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use App\Models\Gallery;
use PDOException;
use RuntimeException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Utils\Http;

/**
 * Gallery controller
 */
#[Route('/gallery')]
class GalleryController extends Controller
{
  /**
   * The aspect ratio of gallery images
   */
  private const ASPECT_RATIO = (16 / 9);

  /**
   * Allowed extensions for gallery images
   */
  private const ALLOWED_EXT = [
    'jpeg' => 'imagejpeg',
    'png' => 'imagepng',
    'webp' => 'imagewebp',
    'gif' => 'imagegif'
  ];

  /**
   * The base url of the public path for the gallery
   */
  private const BASE_URL = 'assets/gallery';

  /**
   * Get the gallery (images list), ordered by date added
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   * @throws PDOException if any PDO error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('[/]', 'GET')]
  public function all(Request $req, Response $res): Response
  {
    $gallery = Gallery::orderBy('added');
    return self::send($res, $gallery);
  }

  /**
   * Post a new image to the gallery
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   * @throws ValidationException when there are errors in the data
   * @throws UnknownException when an unkown error occurs
   * @throws PDOException if any PDO error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('[/]', 'POST', admin: true)]
  public function add(Request $req, Response $res): Response
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

  /**
   * Delete an image from the gallery
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   * @throws NotFoundException if the image does not exist
   * @throws UnknownException when an unkown error occurs
   * @throws PDOException if any PDO error occurs
   * @throws RuntimeException on body writing failure
   */
  #[Route('/{id}', 'DELETE', admin: true)]
  public function delete(Request $req, Response $res, array $params): Response
  {
    $deleted = Gallery::delete($params['id']);
    unlink(ROOT . '/public/' . $deleted->src);
    return self::send($res, $deleted);
  }

  /**
   * Upload the image to the server
   * 
   * @param array $file the temporary file received from the request
   * @return string the public path to the uploaded image
   * @throws ValidationException when there are errors with the data
   * @throws UnknownException if any operation fails
   */
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

  /**
   * Resize the image to get the required aspect ratio
   * 
   * @param string $path the public path to the image to resize
   * @param string $ext the image extension
   * @return bool true if the operation was successful
   * @throws UnknownException if any operation fails
   */
  private static function resizeImage(string $path, string $ext): bool
  {
    $image = imagecreatefromstring(file_get_contents($path));
    [$initWidth, $initHeight] = getimagesize($path);

    if (!$image) {
      throw new UnknownException('could not create image from string');
    }

    [$width, $height] = $initWidth > $initHeight
      ? [$initHeight * self::ASPECT_RATIO, $initHeight]
      : [$initWidth, $initWidth / self::ASPECT_RATIO];

    [$x, $y] = [($initWidth - $width) / 2, ($initHeight - $height) / 2];

    $resized = imagecrop($image, compact('x', 'y', 'width', 'height'));

    if (!$resized) {
      throw new UnknownException('could not crop image');
    }

    return call_user_func_array(self::ALLOWED_EXT[$ext], [$resized, $path]);
  }
}
