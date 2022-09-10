<?php

require_once "Database.class.php";

class Wireguard
{
    private $device;
    public function __construct($device)
    {
        $this->device =$device;
        $this->db =Database::get_connection();
    }


    public function addPeer($publicKey, $email, $reserved, $ip=null)
    {
        if (!$this->hasPeer($publicKey)) {
            $ipnet = new IPNetwork($this->getCIDR(), $this->device);
            $next_ip = $ipnet->getNextIP($email, $ip);
            $cmd = "sudo wg set $this->device peer \"$publicKey\" allowed-ips \"$next_ip/32\"";
            system($cmd, $result);
            system("sudo wg-quick save $this->device", $result1);
            if ($result == 0 and $result1 == 0) {
                return $ipnet->allocateIP($next_ip, $email, $publicKey, boolval($reserved));
            } else {
                return false;
            }
        } else {
            throw new Exception("Peer already exists");
        }
    }

    public function hasPeer($public)
    {
        return count($this->getPeer($public)) >= 1;
    }
    public function removePeer($publicKey)
    {
        $cmd = "sudo wg set $this->device peer \"$publicKey\" remove";
        $result =0;
        system($cmd, $result); //check return code from system echo $?
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
        foreach ($interfaceOut as $value) {
            $value = trim($value);
            $data = explode(':', $value);
            $interface[trim($data[0])] = trim($data[1]);
        }

        foreach ($peersOut as $value) {
            $value =trim($value);
            if (strlen($value)>1) {
                if ($this->startsWith($value, 'peer')) {
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
        foreach ($result as $value) { //making sure that peer occured one time
            if (!empty($value)) {
                $entry = array();
                $value=trim($value);
                if ($this->startsWith($value, 'peer')) {
                    $peerCount ++;
                    if ($peerCount>=2) {
                        break;
                    }
                }
                $data =explode(': ', $value);
                $peer[$data[0]] = $data[1];
            }
        }
        return $peer;
    }

    public function getCIDR()
    {
        $cmd = "sudo cat /etc/wireguard/$this->device.conf | head -n 3";
        $line = trim(shell_exec($cmd));
        $lines = explode(PHP_EOL, $line);
        foreach ($lines as $line) {
            $line = explode('=', $line);
            if (trim($line[0]) == "Address") {
                return trim($line[1]);
            }
        }
    }

    public function reserve($ip, $email)
    {
        $ipnet = new IPNetwork($this->getCIDR(), $this->device);
        return $ipnet->reserveIP($email, $ip, true);
    }

    public function unreserve($ip, $email)
    {
        $ipnet = new IPNetwork($this->getCIDR(), $this->device);
        return $ipnet->reserveIP($email, $ip, false);
    }
}
