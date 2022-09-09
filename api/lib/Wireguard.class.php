<?php 
require_once "Database.class.php";

class Wireguard {
    private $device;
    public function __construct($device)
    {
        $this->device =$device;
        $this->db =Database::get_connection();
    }

    public function getPeers()
    {

    }  
    public function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    public function getPeer($public) //get a single peer
    {
        $cmd = "sudo wg show wg0 | grep -A4  '$public'";
        $op = shell_exec($cmd);
        $result =explode(PHP_EOL, $op); //seperate using delimiter \n
        $peer =array();
        $peerCount = 0;
        foreach ($result as $value) //making sure that peer occured one time
        {
            if (!empty($value))
            {   

                $entry = array();
                $value=trim($value);
                if($this->startsWith($value,'peer'))
                {
                    $peerCount ++;
                    if($peerCount>=2)
                    {
                        break;
                    }
                }
                $data =explode(': ', $value);
                $peer[$data[0]] = $data[1];
                
            }
        }
        return $peer;
    }


}