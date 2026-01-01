<?php

    class billet {
        
        //  Attributs
        private int $id;
        private int $matchId;
        private int $acheteurId;
        private string $categorie;
        private int $numeroPlace;
        private float $prix;
        private string $codeTicket; 
        private string $dateAchat;

        //  Constructeur

        public function __construct(int $id,int $matchId,int $acheteurId,string $categorie,int $numeroPlace,float $prix,string $dateAchat) {

            $this->id = $id;
            $this->matchId = $matchId;
            $this->acheteurId = $acheteurId;
            $this->categorie = $categorie;
            $this->numeroPlace = $numeroPlace;
            $this->prix = $prix;
            $this->dateAchat = $dateAchat;
            $this->codeTicket = uniqid('TICKET_');
        }

        //   QR Code
        public function genererCodeTicket(): string {
            return $this->codeTicket;
        }

        //  Génération du PDF
        public function genererPDF(): bool {
            // Ici on utilisera une librairie PDF (ex: FPDF ou TCPDF)
            // Le PDF contiendra :
            // - infos du match
            // - catégorie
            // - numéro de place
            // - QR code / identifiant

            return true;
        }

        // Envoi du billet par email
        public function envoyerParEmail(string $email): bool {
            // Ici on utilisera PHPMailer
            // Pièce jointe : PDF du ticket

            return true;
        }

        // Getters essentiels
        
        public function getMatchId(): int { 
            return $this->matchId; 
        }

        public function getAcheteurId(): int { 
            return $this->acheteurId; 
        }

        public function getCategorie(): string { 
            return $this->categorie; 
        }

        public function getNumeroPlace(): int { 
            return $this->numeroPlace; 
        }

        public function getPrix(): float { 
            return $this->prix; 
        }
    }
?>
