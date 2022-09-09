<?php
${basename(__FILE__, '.php')} = function () {
    $username = $this->_request['username'];
    $password  = $this->_request['password'];
    $email  = $this->_request['email'];
    if ($this->get_request_method()=="POST" and isset($username) and isset($password) and isset($email)) {
        try {
            
            $s = new Signup($username, $password, $email);
            $data = [
                "message" => "Signup success",
                "userid" => $s->get_insert_id()
            ];
            $this->response($this->json($data), 200);
        } catch (Exception $e) {
            $data = [
                "error" => $e->getMessage()
            ];
            $this->response($this->json($data), 409);
        }
    } else {
        $data = [
            "error" => "Bad request"
        ];
        $data = $this->json($data);
        $this->response($data, 400);
    }
};
