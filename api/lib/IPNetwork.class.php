<?php

require_once "Database.class.php";

class IPNetwork
{
    private $db;
    private $collection;
    private $network;
    public function __construct($cidr)
    {
        $this->cidr = $cidr;
        $this->db =new Database();
        $this->collection =$this->db->getMongoDB('karthik_wgapi')->networks;
        $this->network =$this->getNetwork();
    }

    public function getNetwork()
    {
        if (!$this->network) {
            $val = $this->collection->findone([
                  'cidr' => $this->cidr
               ]);
            return $this->db->getArray($val);
        } else {
            return $this->network;
        }
    }

    public function constructNetworkFile()
    {
        $ipFile =$this->getNetworkFilePath();
        $cmd = ' nmap -sL -n '.$this->cidr.' | awk \'/Nmap scan report /{print $NF}\' >'.$ipFile;
        return system($cmd);
    }

    public function getNetworkFilePath()
    {
        $fileName = str_replace('.', '_', $this->cidr);
        $fileName = str_replace('/', '_', $fileName);
        $ipFile = $_SERVER['DOCUMENT_ROOT'] ."/api/networks/".$fileName;
        return $ipFile;
    }

    public function syncNetworkFile()
    {
        if (file_exists($this->getNetworkFilePath($this->wgdevice))) {
            $data = file_get_contents($this->getNetworkFilePath($this->wgdevice));
            $data = explode(PHP_EOL, $data);
            $data = array_slice($data, 2, count($data) - 4); //avoid 1st two and lastt two ip
            $documents = array();
            $id = $this->getNextInsertID();
            foreach ($data as $datum) {
                if (empty($datum)) {
                    continue;
                }
                $val = [
                    '_id' => $id++,
                    'network_cidr' => $this->cidr,
                    'ip_addr' => $datum,
                    'wgdevice' => $this->wgdevice,
                    'allocated' => false,
                    'creationTime' => time(),
                    'allocationTime' => '',
                    'public_key' => '',
                    'private_key' => '',
                    'reserved' => false
                ];
                array_push($documents, $val);
            }
            return $this->collection->insertMany($documents);
        } else {
            throw new Exception('Network file not present.');
        }
    }

  public function getNextIP($email=null, $ip = null)
  {
      if ($ip and $email) {
          $result = $this->collection->findOne([
              "allocated" => false,
              "reserved" => true,
              "ip_addr" => $ip,
              'email' => $email,
              "wgdevice" => $this->wgdevice
          ], [
              "sort" => [
                  'id'=> 1
              ]
          ]);
          if (!$result) {
              $result = $this->collection->findOne([
                  "allocated" => false,
                  "reserved" => false,
                  "wgdevice" => $this->wgdevice
              ], [
                  "sort" => [
                      'id'=> 1
                  ]
              ]);
          }
      } else {
          $result = $this->collection->findOne([
              "allocated" => false,
              "reserved" => false,
              "wgdevice" => $this->wgdevice
          ], [
              "sort" => [
                  'id'=> 1
              ]
          ]);
      }
      return $result['ip_addr'];
  }

  public function getNextInsertID()
  {
      $last_ip = $this->collection->findOne([], [
          'limit' => 1,
          'sort' => ['_id' => -1],
      ]);
      return $last_ip['_id'] + 1;
  }

    public function allocateIP($ip, $email, $public_key, $reserved)
    {
        try {
            $result = $this->collection->updateOne([
                'ip_addr' => $ip,
                'wgdevice' => $this->wgdevice
            ], [
                '$set' => [
                    'allocated' => true,
                    'email' => $email,
                    'public_key' => $public_key,
                    'reserved' => $reserved
                ]
            ]);
            return $ip;
        } catch (Exception $e) {
            return false;
        }
    }

    public function reserveIP($email, $ip, $reserve=true)
    {
        try {
            $result = $this->collection->updateOne([
                'ip_addr' => $ip,
                'wgdevice' => $this->wgdevice,
                'email' => $email
            ], [
                '$set' => [
                    'reserved' => $reserve
                ]
            ]);
            return boolval($result->getModifiedCount());
        } catch (Exception $e) {
            return false;
        }
    }


    public function getAll()
    {
        return iterator_to_array($this->collection->find(
            [
                '$and' => [
                    [
                    'email'=>[
                        '$ne' => ""
                    ],],[
                    'email'=>[
                        '$exists' => true
                    ],]
                ],
                'wgdevice' => $this->wgdevice
            ]
        ));
    }

    // public function getIP($key)
    // {
    // }

    // public function generateIPFromCIDR()
    // {
    // }

    // public function getUser($ip)
    // {
    // }
}
