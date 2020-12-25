<?php

namespace App\Controllers;

use App\Exceptions\LoggedOutException;
use App\Models\Connections;
use App\Utils;
use Exception;
use GUMP;

class Controller
{
    private $viewPath;

    public function __construct(string $viewPath)
    {
        $this->viewPath = $viewPath;
    }

    private static function answer($success, $arg, int $responseCode)
    {
        self::headers([
            'Content-Type' => 'application/json; charset=UTF-8',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => 'http://localhost:8000'
        ]);

        http_response_code($responseCode);

        echo json_encode($success ? $arg : ['message' => $arg]);

        die();
    }

    protected static function send($value, int $responseCode = 200)
    {
        self::answer(true, $value, $responseCode);
    }

    protected static function error($message, int $responseCode)
    {
        self::answer(false, $message, $responseCode);
    }

    protected static function readData()
    {
        parse_str(file_get_contents('php://input'), $res);
        return $res;
    }

    protected static function headers(array $headers)
    {
        foreach ($headers as $key => $value)
            self::addHeader("$key:$value");
    }

    private static function addHeader(string $header)
    {
        header($header, false);
    }

    protected static function validate(array $data, array $rules, array $filters = [])
    {
        try {
            return Utils::validate($data, $rules, $filters);
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }
    }

    protected static function isLoggedIn()
    {
        try {
            return Connections::isValid(self::token());
        } catch (LoggedOutException $e) {
            return false;
        }
    }

    protected static function token()
    {
        if (empty($_COOKIE['token']))
            throw new LoggedOutException();

        return $_COOKIE['token'];
    }

    protected function watchdog()
    {
        try {
            return self::token();
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }
    }

    protected function getView($view, $args)
    {
        extract($args);
        ob_start();
        require "{$this->viewPath}/$view.php";
        return ob_get_clean();
    }
}
