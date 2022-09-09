<?php
require_once "Auth.class.php";
require_once "User.class.php";
require_once "Database.class.php";
//can construct w/o a refresh token for a new session
//can construct with refresh token for refresh sesion
class OAuth
{
    private $db;
    private $refresh_token =null;
    private $access_token =null;
    private $valid_for;
    private $username;
    private $user;
    
    public function __construct($token = null)
    {
        $this->db = Database::get_connection();
        if ($token != null) {
            if ($this->startsWith($token, 'a.')) {
                $this->access_token = $token;
            } elseif ($this->startsWith($token, 'r.')) {
                $this->refresh_token = $token;
            } else {
                $this->setUsername($token);
            }
        }
    }


    public function setUsername($username)
    {
        $this->username =$username;
        $this->user =new User($username);
    }
    public function getUsername()
    {
        return $this->username;
    }

    public function authenticate()
    {
        if ($this->access_token!=null) {
            $query ="select * from session WHERE access_token ='$this->access_token';";
            $result = mysqli_query($this->db, $query);
            if ($result) {
                $data =mysqli_fetch_assoc($result);
                $created_at = strtotime($data['created_at']);
                $expires_at = $created_at + $data['valid_for'];
                if (time() <= $expires_at) {
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $this->username = $_SESSION['username'] = $data['username'];
                    $_SESSION['token'] = $this->access_token;
                    return true;
                } else {
                    throw new Exception("expired token");
                }
            } else {
                throw new Exception("error" + mysqli_error($this->db));
            }
        }
    }

    public function newSession($valid_for =7200, $reference_token ='auth_grant')
    {
        if ($this->username == null) {
            throw new Exception('username not set for oauuth');
        }
        $this->valid_for =$valid_for;
        $this->access_token ='a.'.Auth::generateRandonHash(32);
        if ($reference_token =='auth_grant') {
            $this->refresh_token ='r.'.Auth::generateRandonHash(32);
        } else {
            $this->refresh_token ='d.'.Auth::generateRandonHash(16);
        }
       
        $query = "INSERT INTO `session` (`username`, `access_token`,`refresh_token`,`valid_for`,`reference_token`, `created_at`)
        VALUES ('$this->username', '$this->access_token','$this->refresh_token', '$this->valid_for','$reference_token',now() );";
        
        if (mysqli_query($this->db, $query)) {
            return array(
                'access_token'=>$this->access_token,
                'valid_for'=>$this->valid_for,
                'reference_token'=>$reference_token,
                'refresh_token'=>$this->refresh_token,
                'type'=>'api'

            );
        } else {
            throw new Exception("unable to create new session".mysqli_error($this->db));
        }
    }

    public function refreshAccess()
    {
        if ($this->refresh_token !=null and !$this->startsWith($this->refresh_token, 'd.')) {  //for dummy token it should nt givve a token
            $query ="select * from session WHERE refresh_token ='$this->refresh_token';";
            $result = mysqli_query($this->db, $query);
        

            if ($result) {
                $data =mysqli_fetch_assoc($result);
                $this->username =$data['username'];
                if ($data['valid'] ==  1) {
                    return $this->newSession(7200, $this->refresh_token);
                } else {
                    throw new Exception("expired token");
                }
            } else {
                throw new Exception("error" + mysqli_error($this->db));
            }
        } else {
            throw new Exception("invalid rquest");
        }
    }

    private function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }
}
