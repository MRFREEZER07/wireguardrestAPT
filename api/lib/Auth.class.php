<?php
require_once "User.class.php";
require_once "OAuth.class.php";
require_once "Database.class.php";
class Auth
{
    private $db;
    public $istoken =false;
    private $loginTokens =null;
    private $oauth;


    public function __construct($username, $password=null)
    {
        $this->db = Database::get_connection();

        if ($password ==null) {
            $this->token =$username;
            $this->isTokenAuth =true;
        } else {
            $this->username =$username; //it might be username or email
            $this->password =$password;
        }
        if ($this->isTokenAuth) {
            $this->oauth =new OAuth($this->token);
            $this->oauth->authenticate();
        } else {
            $user =new User($username);
            $hash = $user->getPasswordHash();
            $this->username =  $user->getName();
            if (password_verify($this->password, $hash)) {
                //verify user is active
                if (!$user->isActive()) {
                    throw new Exception("please check ur email and active ur acc..");
                } else {
                    $this->loginTokens = $this->addSession(7200);
                }
            } else {
                throw new Exception("password doesn't match");
            }
        }
    }
    //return the username of authenticated user
    public function getUsername()
    {
        if ($this->oauth->authenticate()) {
            return $this->oauth->getUsername();
        } else {
            return "a";
        }
    }

    public function getOAuth()
    {
        return $this->oauth;
    }



    public function getAuthTokens()
    {
        return $this->loginTokens;
    }

    private function addSession()
    {
        $oauth = new OAuth();
        $oauth->setUsername($this->username);
        $session=$oauth->newSession();
        return $session;
    }

    public static function generateRandonHash($len)
    {
        $bytes =openssl_random_pseudo_bytes($len, $cstrong);
        return bin2hex($bytes);
    }
}
