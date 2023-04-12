<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $path = explode('/', $_SERVER['REQUEST_URI']);

        $sql = "SELECT * FROM lot_prices WHERE type = :type";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':type', $path[4]);
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);   
        echo json_encode($response);


        break;
    case "POST":
            
        
        break;
    case "PUT":


        break;
    case "DELETE":
        break;


}


?>