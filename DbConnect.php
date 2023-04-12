
<?php

    include 'headers.php';
    /**
    * Database Connection
    */
    class DbConnect {
        private $server = 'localhost';
        private $dbname = 'heavens_gate';
        private $user = 'root';
        private $pass = '';
        //digitez_heavens_gate
        //graveyardcare
        //graveyardcare1221
        public function connect() {
            try {
                $conn = new PDO('mysql:host=' .$this->server .';dbname=' . $this->dbname, $this->user, $this->pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $conn;
            } catch (\Exception $e) {
                echo "Database Error: " . $e->getMessage();
            }
        }
        
    }
?>