<?php

namespace App\Controllers;

use App\Attributes\Route;
use App\Exceptions\LoggedOutException;
use App\Models\Connections;
use App\Config;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use App\Middlewares\AuthMiddleware;
use App\Utils;
use PHPMailer\PHPMailer\PHPMailer;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class UsersController extends Controller
{
  #[Route('/login', 'POST')]
  public function login(Request $req, Response $res)
  {
    if (AuthMiddleware::isLoggedIn($req))
      return $res->withStatus(200);

    $credentials = [];

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $authorization = substr($_SERVER['HTTP_AUTHORIZATION'], 6);
      [$login, $password] = explode(':', base64_decode($authorization));
      $credentials = compact('login', 'password');
    }

    $v = Utils::validate($credentials, [
      'required' => ['login', 'password']
    ], [
      'login' => 'Le login',
      'password' => 'Le mot de passe'
    ]);

    if (!$v->validate())
      throw new ValidationException($v->errors());

    [$user, $pw] = [$credentials['login'], $credentials['password']];

    if ($user != $_ENV['ADMIN_USER'] || $pw != $_ENV['ADMIN_PW']) {
      sleep(2); // prevent brutforce attacks
      return $res
        ->withHeader('WWW-Authenticate', 'Basic realm="Dashboard"')
        ->withStatus(401);
    }

    $connection = Connections::create();
    $cookie = self::tokenToCookie($connection->token);

    return $res
      ->withHeader('Set-Cookie', $cookie)
      ->withStatus(200);
  }

  #[Route('/logout', 'POST')]
  public function logout(Request $req, Response $res)
  {
    try {
      Connections::revoke(self::extractToken($req));
    } catch (LoggedOutException $e) {
    }

    $cookie = self::tokenToCookie();

    return $res
      ->withHeader('Set-Cookie', $cookie)
      ->withStatus(205);
  }

  private static function extractToken(Request $req): string
  {
    $cookies = $req->getCookieParams();

    if (!array_key_exists('token', $cookies))
      throw new LoggedOutException();

    return $cookies['token'];
  }

  private static function tokenToCookie(string $token = ''): string
  {
    $maxAge = Config::TOKEN_LIFETIME;
    $https = 'Secure';

    if (empty($token))
      $maxAge = 0;

    if (Config::ENV_IS_DEV())
      $https = '';

    return "token=$token; Max-Age=$maxAge; HttpOnly; $https";
  }

  #[Route('/contact', 'POST')]
  public function contact(Request $req, Response $res)
  {
    $data = $req->getParsedBody();

    $v = Utils::validate($data, [
      'required' => ['name', 'email', 'content'],
      'email' => ['email']
    ], [
      'name' => "Le prénom",
      'email' => "L'adresse email",
      'content' => "Le message"
    ]);

    if (!$v->validate())
      throw new ValidationException($v->errors());

    if (!self::sendMail($data))
      throw new UnknownException();

    return self::send($res, ['message' => 'Votre message a été envoyé avec succès.']);
  }

  private static function sendMail(array $data): bool
  {
    $data = Utils::filterKeys($data, ['name', 'email', 'content']);
    $data['content'] = htmlentities($data['content']);

    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->Mailer = $_ENV['MAILER'];
    $mail->Host = $_ENV['MAIL_HOST'];
    $mail->Port = $_ENV['MAIL_PORT'];
    $mail->SMTPAuth = false;
    $mail->SMTPAutoTLS = false;
    $mail->setFrom($data['email']);
    $mail->addAddress('contact@aikido-roncq.fr');
    $mail->isHTML();
    $mail->Subject = 'Nouveau message via aikido-roncq.fr';
    $mail->Body = self::getView('mail', $data);

    return $mail->send();
  }
}
