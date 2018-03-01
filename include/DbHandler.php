<?php
/**
 *
 * @About:      Database connection manager class
 * @File:       DbConnect.php
 * @Date:       $Date:$ Feb-2018
 * @Version:    $Rev:$ 1.0
 * @Developer:  Mauricio Vater (mauvater2@gmail.com)
 **/
class DbHandler {
 
    private $conn;
 
    function __construct() {
        require_once dirname(__FILE__) . './DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
 
    public function createAuto($array)
    {
        //you can insert the new localization here.
    }
 
}
 
?>