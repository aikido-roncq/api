<?php

namespace App\Controllers;

use App\Attributes\Route;
use App\Exceptions\LoggedOutException;
use App\Exceptions\NotFoundException;
use App\Models\Connections;
use App\Exceptions\UnknownException;
use App\Exceptions\ValidationException;
use App\Middlewares\AuthMiddleware;
use Exception;
use Utils\Validation;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Utils\Arrays;
use Utils\Http;
use Utils\Logger;

/**
 * Controller dealing with users related actions
 */
class UsersController extends Controller
{
  /**
   * Login to the application
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   * @throws ValidationException on data error
   * @throws PDOException on PDO error
   * @throws UnknownException on unknown error
   * @throws RuntimeException on body writing failure
   */
  #[Route('/login', 'POST')]
  public function login(Request $req, Response $res): Response
  {
    if (AuthMiddleware::isLoggedIn($req)) {
      Logger::info('already logged in');
      return $res->withStatus(Http::OK);
    }

    $credentials = [];

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $authorization = substr($_SERVER['HTTP_AUTHORIZATION'], 6);
      [$login, $password] = explode(':', base64_decode($authorization));
      $credentials = compact('login', 'password');
    }

    $v = Validation::validate($credentials, [
      'required' => ['login', 'password']
    ], [
      'login' => 'Le login',
      'password' => 'Le mot de passe'
    ]);

    if (!$v->validate()) {
      throw new ValidationException('credentials not passing validation', $v->errors());
    }

    [$user, $pw] = [$credentials['login'], $credentials['password']];

    if ($user != $_ENV['ADMIN_USER'] || $pw != $_ENV['ADMIN_PW']) {
      Logger::error('invalid credentials');
      sleep(2); // prevent brutforce attacks
      return $res
        ->withHeader('WWW-Authenticate', 'Basic realm="Dashboard"')
        ->withStatus(Http::UNAUTHORIZED);
    }

    $connection = Connections::insert();

    Logger::info('new login recorded');

    return self::send($res, ['token' => $connection->token]);
  }

  /**
   * Logout from the application
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   * @throws ValidationException on data error
   * @throws PDOException on PDO error
   * @throws UnknownException on unknown error
   */
  #[Route('/logout', 'POST')]
  public function logout(Request $req, Response $res): Response
  {
    try {
      $token = AuthMiddleware::getToken($req);
      Connections::revoke($token);
    } catch (NotFoundException | LoggedOutException $e) {
      Logger::info('user was not logged in');
    }

    return $res->withStatus(Http::RESET_CONTENT);
  }

  /**
   * Send a message to the team
   * 
   * @param Request $req the request
   * @param Response $res the current response
   * @return Response the final response
   * @throws ValidationException on data error
   * @throws UnknownException on unknown error
   * @throws Exception if the any mail fails to send
   */
  #[Route('/contact', 'POST')]
  public function contact(Request $req, Response $res): Response
  {
    $data = $req->getParsedBody();

    $v = Validation::validate($data, [
      'required' => ['name', 'email', 'content'],
      'email' => ['email']
    ], [
      'name' => "Le prénom",
      'email' => "L'adresse email",
      'content' => "Le message"
    ]);

    if (!$v->validate()) {
      throw new ValidationException('contact data not valid', $v->errors());
    }

    self::sendMail($data, [
      'from' => $data['email'],
      'to' => 'contact@aikido-roncq.fr',
      'subject' => 'Nouveau message via aikido-roncq.fr',
      'view' => 'mail',
    ]);

    self::sendMail($data, [
      'from' => 'no-reply@aikido-roncq.fr',
      'to' => $data['email'],
      'subject' => 'Prise en compte de votre message',
      'view' => 'confirm',
    ]);

    return self::send($res, [
      'message' => 'Votre message a été envoyé avec succès.'
    ]);
  }

  /**
   * Send a mail from given data
   * 
   * @param array $data mail data (email address, content...)
   * @param array $options mail options (sender, recipient, subject...)
   * @throws Exception if the mail fails to send
   * @throws UnknownException if the mail fails to send
   */
  private static function sendMail(array $data, array $options): void
  {
    $data = Arrays::filterKeys($data, ['name', 'email', 'content']);
    $data['content'] = htmlentities($data['content']);

    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->Mailer = $_ENV['MAILER'];
    $mail->Host = $_ENV['MAIL_HOST'];
    $mail->Port = $_ENV['MAIL_PORT'];
    $mail->SMTPAuth = false;
    $mail->SMTPAutoTLS = false;
    $mail->setFrom($options['from']);
    $mail->addAddress($options['to']);
    $mail->isHTML();
    $mail->Subject = $options['subject'];
    $mail->Body = self::getView($options['view'], $data);

    if (!$mail->send()) {
      $recipient = $options['to'];
      throw new UnknownException("could not send mail to $recipient");
    }

    Logger::info('a new email has been sent');
  }
}
