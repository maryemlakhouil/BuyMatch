<?php 
    abstract class User{
        
        protected int $id;
        protected string $nom;
        protected string $email;
        protected string $password;
        protected string $role;
        protected bool $estActif;

        // constructure

        public function __construct(int $id,string $nom,string $email,string $password,string $role,bool $estActif=true){

            $this->id = $id;
            $this->nom = $nom;
            $this->email = $email;
            $this->password = $password;
            $this->role = $role;
            $this->estActif = $estActif;
        }

        // getters

        public function getId(): int {
            return $this->id;
        }

        public function getNom() : string {
            return $this->nom;
        }

        public function getEmail() : string {
            return $this->email;
        }

        public function getPassword() : string {
            return $this->password;
        }

        public function getRole() : string {
            return $this->role;
        }

        public function getActif() : bool {
            return $this->estActif;
        }

        // setters 

        public function setNom(string $nom){
            $this->nom=$nom;
        }

        public function setEmail(string $Email) : void {
            if(filter_var($Email,FILTER_VALIDATE_EMAIL)){
                $this->email=$Email;
            }
        }

        public function setPassword(string $nvpassword) : void {
            $this->password = password_hash($nvpassword ,PASSWORD_DEFAULT);
        }

        // method de cette classe 

        public function seConnecter(): bool{

            if ($this->estActif) {
                $_SESSION['user_id'] = $this->id;
                $_SESSION['role'] = $this->role;
                $_SESSION['nom'] = $this->nom;
                return true;
            }
            return false;
        }

        public function seDeconnecter(): void{
            session_unset();
            session_destroy();
        }
    }

?>