<?php
${basename(__FILE__, '.php')} = function () {
if($this->get_request_method() == "POST")
{
   

    try{
        $wg = new Wireguard('wg0');
        $data = $wg->getPeers();
        
        $data =$this->json($data);
        $this->response($data,200);
    }catch(Exception $e)
    {
        $data =[
            "error"=>$e->getMessage()
        ];
        $data =$this->json($data);
        $this->response($data,400);
    }
}
else{
    $data = [
        "error" => "Bad request"
    ];
    $data =$this->json($data);
    $this->response($data,400);
}
};