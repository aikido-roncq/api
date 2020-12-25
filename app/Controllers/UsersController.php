<?php

namespace App\Controllers;

use App\Exceptions\LoggedOutException;
use App\Models\Connections;
use App\Config;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class UsersController extends Controller
{
    public function login()
    {
        $success = ['message' => 'Connexion réussie.'];

        if ($this->isLoggedIn())
            self::send($success, 200);

        self::validate($_SERVER, [
            'PHP_AUTH_USER' => 'required',
            'PHP_AUTH_PW' => 'required'
        ]);

        [$user, $pw] = [$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']];

        if ($user != $_ENV['ADMIN_USER'] || $pw != $_ENV['ADMIN_PW']) {
            sleep(2); // brutforce attacks protection
            self::headers(['WWW-Authenticate' => 'Basic realm="aikido"']);
            self::error("Nom d'utilisateur ou mot de passe incorrect.", 401);
        }

        try {
            $record = Connections::create();
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }

        self::setCookieToken($record->token);
        self::send($success, 200);
    }

    public function logout()
    {
        try {
            Connections::revoke(self::token());
        } catch (LoggedOutException $e) {
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }

        self::headers(['Set-Cookie' => 'token=; Max-Age=0; HttpOnly']);
        self::send(['message' => 'Déconnexion réussie.'], 205);
    }

    public static function setCookieToken($token)
    {
        $exp = Config::TOKEN_LIFETIME;
        self::headers(['Set-Cookie' => "token=$token; Max-Age=$exp; HttpOnly"]);
    }

    public function contact()
    {
        $validData = self::validate($_POST, [
            'name' => 'required',
            'email' => 'required|valid_email',
            'content' => 'required'
        ], [
            'name' => 'trim',
            'email' => 'trim|sanitize_email',
            'content' => 'trim'
        ]);

        $this->sendMail($validData);
    }

    /* --------------------------------------------------------------------- */

    public function sendMail($data)
    {
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
        $mail->Body = $this->getView('mail', $data);

        if ($mail->send())
            self::send(['message' => 'Votre message a été envoyé avec succès.']);
        else
            self::error('Une erreur est survenue. Merci de réessayer plus tard.', 500);
    }
}
