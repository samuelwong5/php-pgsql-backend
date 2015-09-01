<?php

class LoginUtils {
 
    private $db;
    private $conn;
    
    function __construct() {
        require_once 'DB_Connection.php';
        $this->db = new DB_Connection();
        $this->conn = $this->db->connect();
    }
    
    function __destruct() {
        $this->db->close();
    }
    
    public function userLogin($user, $password) {
       $sql = "SELECT * FROM users WHERE username = '$user' AND password = crypt($password, password)";
       $rows = pg_query($this->conn, $sql)
       if ($rows) {
           die("Database connection failed.");
       }
       $result = pg_fetch_row($rows);
       return $result;     
    }
    
    public function userExists($user) {
        $sql = "SELECT * FROM users WHERE username = '$user'";
        $rows = pg_query($this->conn, $sql);
        return pg_num_rows($rows) > 0;
    }
    
    public function userCreate($user, $password) {
        $sql = "INSERT into users (user, password) VALUES ('$user',crypt($password, gen_salt('bf')))";
        $result = pg_query($this->conn, $sql);
        return $result;
    }
    
    public function userChangePassword($user, $password) {
        $sql = "UPDATE table SET password = crypt('password',gen_salt('bf')) WHERE user='$user'";
        $result = pg_query($this->conn, $sql);
        return $result;
    }
    
}
 
 
$action = $_POST("action");
$user = pg_escape_string($_POST("user"));
$password = pg_escape_string($_POST("password"));
$db = new LoginUtils();

// JSON response 
$response = array("action" => $action, "error" => false);

if ($action == "login") {
    // Check if login username and password match
    $cred = $db->userLogin($user, $password);
    if (!$cred) {
        $response["error"] = true;
        $response["error_msg"] = "Incorrect username or password.";
    } else {
        $response["user"]["id"] = $cred["id"];
        $response["user"]["username"] = $cred["username"];
    }
} else if ($action == "register") {
    // Check if user exists; if not then add to database
    $exists = $db->userExists($user);
    if ($exists) {
        $response["error"] = true;
        $response["error_msg"] = "Username already exists.";
    } else {
        $cred = $db->userCreate($user, $password);
        if (!$cred) {
            $response["error"] = true;
            $response["error_msg"] = "Registration failed.";
        } else {
            $response["user"]["id"] = $cred["id"];
            $response["user"]["username"] = $cred["username"];
        }
    }
} else {
    $response["error"] = true;
    $response["error_msg"] = "Missing action parameter. [login/register]";
}
echo json_encode($response);
 
?>