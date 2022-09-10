<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/api/lib/Wireguard.class.php';
require_once $_SERVER['DOCUMENT_ROOT']."/api/lib/IPNetwork.class.php";
$wg = new Wireguard('wgdev');
print($wg->getCIDR());
$ip = new IPNetwork($wg->getCIDR(), $wg->device);
//print($ip->getNextInsertID());
print_r($ip->getNetwork());
$ip->constructNetworkFile($wg->device);
try {
    print_r($ip->syncNetworkFile($wg->device));
} catch (Exception $e) {
    print("Network already synced $e");
}
