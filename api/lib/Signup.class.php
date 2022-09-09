<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require($_SERVER['DOCUMENT_ROOT'] . '/api/vendor/autoload.php');


require_once "Database.class.php";
require_once "/var/www/html/wgapi/PHPMailer/src/PHPMailer.php";
require_once "/var/www/html/wgapi/PHPMailer/src/SMTP.php";

require_once "Database.class.php";

class Signup
{
    private $username;
    private $password;
    private $email;

    private $db;

    public function __construct($username, $password, $email)
    {
        $this->db = Database::get_connection();
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $password = $this->hashPassword();

        if ($this->userExists()) {
            throw new Exception("user already exist");
        }
        //for token
        $bytes = random_bytes(16);
        $this->token = bin2hex($bytes); //to verify over email
        $token=$this->token;
        $query = "INSERT INTO `auth` (`username`, `password`, `email`, `active`, `token`, `signup_time`)
            VALUES ('$username', '$password', '$email', '0', '$this->token', now()); ";

        if (!mysqli_query($this->db, $query)) {
            throw new Exception("unable to signup");
        } else {
            $this->id = mysqli_insert_id($this->db);
            $this->sendVerificationMail();
        }
    }


    public function userExists()
    {
        $name =$this->username;
        $query ="SELECT * FROM `auth` WHERE username ='$name';";
        $db =Database::get_connection();
        $result =mysqli_query($db, $query);
        if (mysqli_num_rows($result)>0) {
            return true;
        } else {
            return false;
        }
    }


    public function get_insert_id()
    {
        return $this->id;
    }

    public function hashPassword()
    {
        $options = [
            'cost' => 12,
        ];
        return password_hash($this->password, PASSWORD_BCRYPT, $options);
    }

    public function sendVerificationMail()
    {
        $config_json = file_get_contents('/var/www/env.json');
        $config = json_decode($config_json, true);
        $smtp_pass = $config['smtp_pass'];

        $token =$this->token;
        $mail = new PHPMailer(true);


        //Server settings
        //$mail->SMTPDebug = 1;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mkarthik585@gmail.com';
        $mail->Password   = $smtp_pass;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        //Recipients
        $mail->setFrom('karthik@freezer.org', 'Freezer');
        $mail->addAddress($this->email);


        //Content
        $mail->isHTML(true);
        $mail->Subject = 'verify your account';
        $mail->Body    = "<strong>plz verify your account .     by clicking on this link    <a herf =\"https://kbgrunt.selfmade.lol/verify?token=$token\">clicking here:)</a> or open it manually https://kbgrunt.selfmade.lol/verify?token=$token <strong> ";


        try {
            $mail->send();
        } catch (Exception $e) {
            echo 'Caught exception: '. $e ."\n";
        }
    }

    public static function verify_account($token)
    {
        $query ="SELECT * FROM `auth` WHERE token ='$token';";
        $db =Database::get_connection();
        $result =mysqli_query($db, $query);
        if ($result and mysqli_num_rows($result)==1) {
            $data = mysqli_fetch_assoc($result);
            if ($data['active']==1) {
                throw new Exception("already verified");
            }
            $q = "UPDATE `auth` SET `active` = '1' WHERE `token` = '$token';";


            mysqli_query($db, $q);
            return true;
        } else {
            return false;
        }
    }
}
