<?php

namespace App;

use Dotenv\Dotenv;
use GUMP;

define('ROOT', dirname(__DIR__));

class App
{
    private $viewPath = ROOT . '/app/Views';

    public function __construct()
    {
        Dotenv::createImmutable(ROOT)->load();

        error_reporting($_ENV['APP_ENV'] == 'prod' ? 0 : E_ALL);

        GUMP::add_validator('optional', function () {
            return true;
        }, '');

        (new Router($this->viewPath))

            ->get('/articles', 'Articles#all')
            ->get('/articles/[*:slug]', 'Articles#find')
            ->post('/articles', 'Articles#add')
            ->patch('/articles/[*:slug]', 'Articles#edit')
            ->delete('/articles/[*:slug]', 'Articles#delete')

            ->get('/events', 'Events#all')
            ->get('/events/[i:id]', 'Events#find')
            ->post('/events', 'Events#add')
            ->patch('/events/[i:id]', 'Events#edit')
            ->delete('/events/[i:id]', 'Events#delete')

            ->get('/gallery', 'Gallery#all')
            ->post('/gallery', 'Gallery#add')

            ->post('/login', 'Users#login')
            ->post('/logout', 'Users#logout')
            ->post('/contact', 'Users#contact')

            ->run();
    }
}
