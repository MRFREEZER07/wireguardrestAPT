<?php 
require_once "Database.class.php";

class Wireguard {
    private $device;
    public function __construct($device)
    {
        $this->device =$device;
        $this->db =Database::get_connection();
    }


    public function addPeer($publicKey,$ip)
    {
        $cmd ="sudo wg set $this->device peer \"$publicKey\" allowed-ips \"$ip\"" ;
    }

    public function removePeer($publicKey)
    {
        $cmd = "sudo wg set $this->device peer \"$publicKey\" remove";
        $result =0;
        system($cmd,$result); //check return code from system echo $?
        return $result ==0 ;

    }

    public function getPeers()
    {

    }  
    public function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    public function getPeer($publicKey) //get a single peer
    {   //TODO:handle the peer that not present
        $cmd = "sudo wg show wg0 | grep -A4  '$publicKey'";
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