<?php

    require_once 'User.php';

    class Admin extends User {

        //  Valider un match
        public function validerMatch(int $matchId): bool {

            // Mise à jour du statut du match : "valide"
            // UPDATE matches SET statut = 'valide' WHERE id = ?
            return true;
        }

        //  Refuser un match
        public function refuserMatch(int $matchId): bool {
            // Mise à jour du statut du match : "refuse"
            // UPDATE matches SET statut = 'refuse' WHERE id = ?

            return true;
        }

        //  Activer un utilisateur
        public function activerUtilisateur(int $userId): bool{
            // UPDATE users SET est_actif = 1 WHERE id = ?
            return true;
        }

        //  Désactiver un utilisateur
        public function desactiverUtilisateur(int $userId): bool {
            // UPDATE users SET est_actif = 0 WHERE id = ?
            return true;
        }

        //  Statistiques globales
        public function consulterStatistiquesGlobales(): array {
            // total matchs
            // total billets vendus
            // chiffre d'affaires global
            return [];
        }

        //  Accéder aux commentaires
        public function consulterCommentaires(): array{
            return [];
        }
    }
?>