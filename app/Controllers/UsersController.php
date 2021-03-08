<?php

namespace App\Controllers;

use App\Attributes\Route;
use App\Exceptions\LoggedOutException;
use App\Models\Connections;
use App\Config;
use App\Exceptions\UnknownException;
use App\Utils;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class UsersController extends Controller
{
  #[Route('/login', 'POST')]
  public function login(Request $req, Response $res)
  {
    if (self::isLoggedIn($req))
      return $res->withStatus(200);

    $credentials = [];

    if (array_key_exists('PHP_AUTH_USER', $_SERVER))
      $credentials['login'] = $_SERVER['PHP_AUTH_USER'];

    if (array_key_exists('PHP_AUTH_PW', $_SERVER))
      $credentials['password'] = $_SERVER['PHP_AUTH_PW'];

    $v = Utils::validate($credentials, [
      'required' => ['login', 'password']
    ], [
      'login' => 'Le login',
      'password' => 'Le mot de passe'
    ]);

    if (!$v->validate())
      return self::badRequest($res, $v->errors());

    [$user, $pw] = [$credentials['login'], $credentials['password']];

    if ($user != $_ENV['ADMIN_USER'] || $pw != $_ENV['ADMIN_PW']) {
      sleep(2); // prevent brutforce attacks
      return $res
        ->withHeader('WWW-Authenticate', 'Basic realm="Dashboard"')
        ->withStatus(401);
    }

    try {
      $connection = Connections::create();
    } catch (Exception $e) {
      return self::error($res, $e);
    }

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
    } catch (Exception $e) {
      self::error($res, $e);
    }

    $cookie = self::tokenToCookie();

    return $res
      ->withHeader('Set-Cookie', $cookie)
      ->withStatus(205);
  }

  private static function tokenToCookie(string $token = '')
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
      return self::badRequest($res, $v->errors());

    try {
      self::sendMail($data);
    } catch (Exception $e) {
      return self::error($res, $e);
    }

    return self::send($res, ['message' => 'Votre message a été envoyé avec succès.']);
  }

  private static function sendMail(array $data)
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

    if (!$mail->send())
      throw new UnknownException();
  }
}
