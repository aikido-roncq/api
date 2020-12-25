<?php

namespace App\Controllers;

use App\Models\Articles;
use Exception;

class ArticlesController extends Controller
{
    public function all()
    {
        self::send(Articles::orderBy('date', 'desc'));
    }

    public function find(string $slug)
    {
        try {
            $article = Articles::find($slug);
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }

        self::send($article);
    }

    /* --------------------------------------------------------------------- */

    public function add()
    {
        $this->watchdog();

        try {
            $article = Articles::create($_POST);
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }

        self::send($article, 201);
    }

    public function delete(string $slug)
    {
        $this->watchdog();

        try {
            $article = Articles::delete($slug);
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }

        self::send($article);
    }

    public function edit(string $slug)
    {
        $this->watchdog();

        $_PATCH = self::readData();

        $data = self::validate($_PATCH, [
            'title' => 'optional',
            'content' => 'optional'
        ]);

        try {
            $article = Articles::update($slug, array_filter($data));
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }

        self::send($article);
    }
}
