<?php

class DB_Connection {
 
    public function connect() {
        require_once 'include/Config.php';
        $conn = pg_connect("host=$DB_HOST port=$DB_PORT db=$DB_DATABASE user=$DB_USER password=$DB_PASSWORD");
        mysql_select_db(DB_DATABASE) or die("Unable to establish connection to database.");
        $this->conn = $conn
        return $conn;
    }

    public function close() {
        pg_close($this->conn);
    }
 
}
 
?>