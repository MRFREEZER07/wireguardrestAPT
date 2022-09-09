<?php

${basename(__FILE__, '.php')} = function () {
    if ($this->get_request_method()=="POST" and $this->isAuthenticated()) {
        try {
            $auth = new Auth($this->_request['token']);
            $username =$auth->getUsername();
            $data =[
                'user'=>$this->getUsername(),
                
            ];
            $data = $this->json($data);
            $this->response($data, 200);
        } catch (Exception $e) {
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
