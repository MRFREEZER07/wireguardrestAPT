<?php
${basename(__FILE__, '.php')} = function () {
    $username=$this->_request['username'];
    $password =$this->_request['password'];
    if ($this->isAuthenticated()) {
        $data = [
            "error" => "Already logged in"
        ];
        $data = $this->json($data);
        $this->response($data, 400);
    }

    if ($this->get_request_method()=="POST" and isset($username) and isset($password)) {
        $username=$this->_request['username'];
        $password =$this->_request['password'];

        try {
            $auth =new Auth($username, $password);
            $data = [
                "message"=> "login success",
                "token"=>$auth->getAuthTokens()
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
