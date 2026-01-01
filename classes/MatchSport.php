<?php

class MatchSport{

    //  Attributs
    private int $id;
    private string $equipe1;
    private string $equipe2;
    private string $logoEquipe1;
    private string $logoEquipe2;
    private string $dateHeure;
    private string $lieu;
    private int $duree;
    private int $nbPlacesTotal;
    private int $nbPlacesVendues;
    private array $categories; 
    private string $statut; 

    //  Constructeur

    public function __construct(int $id,string $equipe1,string $equipe2,string $dateHeure,string $lieu,int $nbPlacesTotal,array $categories,string $statut = 'en_attente',int $duree = 90,int $nbPlacesVendues = 0) {

        $this->id = $id;
        $this->equipe1 = $equipe1;
        $this->equipe2 = $equipe2;
        $this->dateHeure = $dateHeure;
        $this->lieu = $lieu;
        $this->duree = $duree;
        $this->nbPlacesTotal = $nbPlacesTotal;
        $this->nbPlacesVendues = $nbPlacesVendues;
        $this->categories = $categories;
        $this->statut = $statut;
    }

    // Places disponibles
    public function getPlacesDisponibles(): int {
        return $this->nbPlacesTotal - $this->nbPlacesVendues;
    }

    //  Vérifier disponibilité
    public function estComplet(): bool{
        return $this->getPlacesDisponibles() <= 0;
    }

    // Calculer la note moyenne 
    public function calculerNoteMoyenne(array $notes): float {

        if (count($notes) === 0) {
            return 0;
        }

        $total = array_sum($notes);
        return round($total / count($notes), 2);
    }

    //  Vérifier si le match est terminé

    public function estTermine(): bool {
        $dateMatch = strtotime($this->dateHeure);
        $finMatch = $dateMatch + ($this->duree * 60);

        return time() > $finMatch;
    }

    //  Getters essentiels
    public function getId(): int { 
        return $this->id;
    }

    public function getEquipe1(): string { 
        return $this->equipe1; 
    }

    public function getEquipe2(): string { 
        return $this->equipe2; 
    }
    
    public function getStatut(): string { 
        return $this->statut; 
    }
}
?>