<?php
/* Variables:
    - $name (user's name)
    - $email (user's email address)
    - $content (message's content)
*/

?>

<!DOCTYPE html>
<html lang="fr">

<body style="font-family: sans-serif">

    <p><strong>De:</strong> <?= $name ?></p>

    <p><?= $content ?></p>

    <br>

    <a href="mailto:<?= $email ?>" style="color: white; text-decoration: none; padding: .6em 1em; border-radius: .2em; background:red">Répondre</a>
</body>

</html>