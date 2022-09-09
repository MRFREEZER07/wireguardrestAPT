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
        $cmd = "sudo wg show $this->device ";
        $op = shell_exec($cmd);
        $result =explode(PHP_EOL, $op); //seperate using delimiter \n
        $interfaceOut = array_slice($result, 0, 4); //seperating the interface as a seperate array
        $peersOut= array_slice($result, 5); //seperating the peers as a seperate array
        $peers =array();
        $interface =array();
        $peerCount = -1; //using peercount as array index in peers
        
        //seperating interface
        foreach ($interfaceOut as $value) 
        {
            $value = trim($value);
            $data = explode(':', $value);
            $interface[trim($data[0])] = trim($data[1]);

        }

        foreach($peersOut as $value){
            $value =trim($value);
            if(strlen($value)>1)
            {
                if($this->startsWith($value,'peer'))               
                {
                    $peerCount++;
                }
                $data =explode(':', $value);
                $peers[$peerCount][trim($data[0])]=trim($data[1]);
            }
        }
        return [
            'interface'=>$interface,
            'peer'=>$peers
        ];
        
    }  
    public function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    public function getPeer($publicKey) //get a single peer
    {   //TODO:handle the peer that not present
        $cmd = "sudo wg show $this->device | grep -A4  '$publicKey'";
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