<?php

${basename(__FILE__, '.php')} = function () {
    if ($this->get_request_method() == "POST") {
        try {
            $device = 'wg0';
            if (isset($this->_request['device'])) {
                $device = $this->_request['device'];
            }
            $wg = new Wireguard($device);
            $ip = new IPNetwork($wg->getCIDR(), $wg->device);
            $data = $this->json(['result'=>$ip->getNextIP()]);
            $this->response($data, 200);
        } catch(Exception $e) {
            $data = [
                "error" => $e->getMessage()
            ];
            $data = $this->json($data);
            $this->response($data, 403);
        }
    } else {
        $data = [
            "error" => "Bad request"
        ];
        $data = $this->json($data);
        $this->response($data, 400);
    }
};
