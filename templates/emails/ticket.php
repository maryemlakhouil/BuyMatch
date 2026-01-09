<?php
/** @var array $ticket */
/** @var array $match */
?>

<h2> Billet confirmé</h2>

<p>Bonjour ,</p>

<p>Votre billet pour le match suivant est confirmé :</p>

<ul>
    <li><strong>Match :</strong> <?= $match['equipe1'] ?> vs <?= $match['equipe2'] ?></li>
    <li><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($match['date_heure'])) ?></li>
    <li><strong>Lieu :</strong> <?= $match['lieu'] ?></li>
    <li><strong>Place :</strong> <?= $ticket['numero_place'] ?></li>
</ul>

<p>
 <a href="http://localhost/BuyMatch/pages/ticket_print.php?ticket_id=<?= $ticket['id'] ?>">
Imprimer mon billet
</a>
</p>

<p>Merci pour votre confiance,<br>BuyMatch</p>
?>