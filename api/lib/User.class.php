<?php
require_once "Database.class.php";
class User
{
    private $db;
    public function __construct($username)
    {
        $this->username =$username;
        $db = Database::get_connection();
        $query = "SELECT * FROM auth WHERE username='$this->username' OR email ='$this->username';";
        $result =mysqli_query($db, $query);
        if (mysqli_num_rows($result)>0) {
            $this->user =mysqli_fetch_assoc($result);
        } else {
            throw new Exception("user not found");
        }
    }
    public function getName()
    {
        return $this->user['username'];
    }

    public function getPasswordHash()
    {
        return $this->user['password'];
    }

    public function isActive()
    {
        return $this->user['active'];
    }

    public function getEmail()
    {
        return $this->user['email'];
    }
}
