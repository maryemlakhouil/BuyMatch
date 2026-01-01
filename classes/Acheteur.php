<?php
require_once 'User.php' ;

    class Acheteur extends User {

        // acheter une billet 
        public function acheterBillet(int $matchId,int $categorieId,int $placeNumero,int $quantite): bool {

            // max 4 billets par match
            if ($quantite < 1 || $quantite > 4) {
                return false;
            }

            // Ici : logique BDD (à implémenter plus tard)
            // - vérifier disponibilité
            // - enregistrer le billet
            // - générer ticket PDF
            // - envoyer email

            return true;
        }
        // Historique des billets

        public function consulterHistorique(): array {
            // Retournera les billets achetés depuis la BDD
            return [];
        }

        public function noterMatch(int $matchId,int $note,string $commentaire): bool {

            if ($note < 1 || $note > 5) {
                return false;
            }

            // Vérifier si le match est terminé
            // Enregistrer la note et le commentaire

            return true;
        }

    }
?>