<?php
${basename(__FILE__, '.php')} = function () {
    $refresh_token=$this->_request['refresh_token'];

    if ($this->get_request_method()=="POST" and isset($refresh_token)) {
        try {
            $auth =new OAuth($refresh_token);
            $data = [
                "message"=> "refresh success",
                "token"=>$auth->refreshAccess(),
            ];
            $data = $this->json($data);
            $this->response($data, 200);
        } catch (Exception $e) {
            $data = [
            "error" => $e->getMessage()
        ];
            $data = $this->json($data);
            $this->response($data, 400);
        }
    } else {
        $data = [
        "error" => "Bad request"
    ];
        $data = $this->json($data);
        $this->response($data, 400);
    }
};
