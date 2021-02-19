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

    $v = Utils::validate($_SERVER, [
      'required' => ['PHP_AUTH_USER', 'PHP_AUTH_PW']
    ], [
      'PHP_AUTH_USER' => 'Le login',
      'PHP_AUTH_PW' => 'Le mot de passe'
    ]);

    if (!$v->validate())
      return self::badRequest($res, $v->errors());

    [$user, $pw] = [$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']];

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

    $token = $connection->token;
    $maxAge = Config::TOKEN_LIFETIME;

    return $res
      ->withHeader('Set-Cookie', "token=$token; Max-Age=$maxAge; HttpOnly; Secure")
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

    return $res
      ->withHeader('Set-Cookie', 'token=; Max-Age=0; HttpOnly; Secure')
      ->withStatus(205);
  }

  #[Route('/contact', 'POST')]
  public function contact(Request $req, Response $res)
  {
    $data = self::readData();

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

  public static function sendMail(array $data)
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
