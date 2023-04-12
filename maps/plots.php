<?php

include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $sql = "SELECT * FROM plot_type";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($response);

        break;
    case "POST":

    

    case "PUT":

        break;

    case "DELETE":



        break;
}

?>