<?php

/**
 * @var string $name the user's name
 * @var string $content the message's content
 */

?>

<!DOCTYPE html>
<html lang="fr">

<body style="font-family: sans-serif">

  <div style="width: 100%; display: flex; justify-content: center">
    <img src="https://api.aikido-roncq.fr/assets/logo.png" style="text-align: center;" width="100">
  </div>

  <p>Bonjour, <strong> <?= $name ?> </strong> ! 👋</p>

  <p>
    Nous avons bien reçu votre message, et nous y répondrons dans les meilleurs délais.
  </p>

  <p>
    Nous vous remercions pour l'intérêt que vous portez au club.
  </p>

  <p>À bientôt !</p>

  <div style="opacity: .8; font-size: .8rem">
    <hr>

    <p>Ceci est un message automatique, merci de ne pas y répondre.</p>

    <p>Copie de votre message :</p>
    <blockquote style="border-left: .2rem solid red; padding-left: 1rem; margin: 1rem">
      <?= $content ?>
    </blockquote>
  </div>

</body>

</html>
