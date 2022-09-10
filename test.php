<?php

require_once "api/lib/Database.class.php";
require_once "api/lib/IPNetwork.class.php";

$ip =new IPNetwork('172.20.0.0/16');
//print_r($ip->getNetwork());
print_r($ip->syncNetworkFile());