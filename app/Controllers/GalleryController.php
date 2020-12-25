<?php

namespace App\Controllers;

use App\Exceptions\UnknownException;
use App\Models\Gallery;
use Exception;
use InvalidArgumentException;

class GalleryController extends Controller
{
    private const ALLOWED_EXT = [
        'jpeg' => 'imagejpeg',
        'png' => 'imagepng',
        'webp' => 'imagewebp',
        'gif' => 'imagegif'
    ];

    public function all()
    {
        try {
            self::send(Gallery::orderBy('added'));
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }
    }

    public function add()
    {
        $this->watchdog();

        $validData = self::validate($_POST + $_FILES, [
            'caption' => 'optional',
            'image' => 'required_file'
        ]);

        try {
            $validData['src'] = self::upload($validData['image']);
            self::send(Gallery::create($validData), 201);
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }
    }

    /* --------------------------------------------------------------------- */
    private static function upload(array $file): string
    {
        $filename = bin2hex(random_bytes(4));
        [$type, $ext] = explode('/', $file['type']);

        if ($type != 'image')
            throw new InvalidArgumentException('Type de fichier invalide', 400);
        elseif (!in_array($ext, array_keys(self::ALLOWED_EXT)))
            throw new InvalidArgumentException('Extension non supportée', 400);

        $publicPath = sprintf('assets/gallery/%s.%s', $filename, $ext);
        $realPath = sprintf('%s/public/%s', ROOT, $publicPath);

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
