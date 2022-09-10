<?php

require('/var/www/html/wgapi/api/vendor/autoload.php');

class Database
{
    public static $db;

    public function __construct()
    {
        if (!extension_loaded('mongodb')) {
            die("database extension not loaded");
        }
        $this->mongoClient = new MongoDB\Client('mongodb://karthik:sridevi21@mongodb.selfmade.ninja/?authSource=users');
        if (!$this->mongoClient) {
            http_response_code(500);
            die("cannot connect to  db");
        }
    }

    public function getMongoDB($db)
    {
        return $this->mongoClient->$db;
    }



    public static function get_connection()
    {
        $config_json = file_get_contents('/var/www/env.json');
        $config = json_decode($config_json, true);
        if (Database::$db != null) {
            return Database::$db;
        } else {
            Database::$db = mysqli_connect($config['db_server'], $config['db_username'], $config['db_password'], $config['db_name']);
            if (!Database::$db) {
                die("Connection failed: ".mysqli_connect_error());
            } else {
                return Database::$db;
            }
        }
    }
    //convert bson to assoc array
    public function getArray($doc)
    {
        return json_decode(json_encode($doc),true);
    }
}
