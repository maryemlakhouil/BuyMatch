<?php

require_once 'User.php';

    class Organisateur extends User{

        //  Créer une demande de match
        public function creerMatch(string $equipe1,string $equipe2,string $logoEquipe1,string $logoEquipe2,string $dateHeure,string $lieu,int $duree,int $nbPlaces,array $categories): bool {

            // max 2000
            if ($nbPlaces > 2000 || $nbPlaces <= 0) {
                return false;
            }

            if (count($categories) > 3) {
                return false;
            }

            if ($duree !== 90) {
                return false;
            }

            // Ici : insertion dans la BDD avec statut = "en_attente"
            // L'admin devra valider

            return true;
        }

        // Consulter les statistiques
        public function consulterStatistiques(): array {
            // billets vendus
            // chiffre d'affaires
            return [];
        }

        //  Consulter les avis et commentaires
        public function consulterAvis(int $matchId): array {
            return [];
        }
    }
?>
