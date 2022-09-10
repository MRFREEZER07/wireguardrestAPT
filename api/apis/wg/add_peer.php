<?php

${basename(__FILE__, '.php')} = function () {
    if ($this->get_request_method() == "POST"  and !empty($this->_request['public_key']) and !empty($this->_request['email'])) {
        $peer =$this->_request['peer'];
        $device = 'wg0';
        try {
            $wg = new Wireguard($device);
            $ipnet =new IPNetwork();
            $data = [

                "result" => $wg->addPeer($peer, $ip)
            ];

            $data =$this->json($data);
            $this->response($data, 200);
        } catch(Exception $e) {
            $data =[
                "error"=>$e->getMessage()
            ];
            $data =$this->json($data);
            $this->response($data, 400);
        }
    } else {
        $data = [
            "error" => "Bad request"
        ];
        $data =$this->json($data);
        $this->response($data, 400);
    }
};
