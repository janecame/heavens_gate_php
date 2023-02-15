<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


$path = explode('/', $_SERVER['REQUEST_URI']);

$response = [];

function setArchived($id, $conn) {

    $sql = "UPDATE orders SET status = 2 WHERE order_id = :order_id";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':order_id', $id);

    if($stmt->execute()) {
        $response = ['status' => 1, 'message' => 'Archived successfully.'];
    } else {
        $response = ['status' => 0, 'message' => 'Archived Failed.'];
    }
    return $response;

}

function setApproved($id, $conn) {
    
    $response = [];

    $sql = "UPDATE orders SET status = 1 WHERE order_id = :order_id";
    $stmt = $conn->prepare($sql);
    
    $stmt->bindParam(':order_id', $id);
    
    if($stmt->execute()) {
        $response = ['status' => 1, 'message' => 'Approved Successfully.'];
    } else {
        $response = ['status' => 0, 'message' => 'Approved Failed'];
    }

    return $response;

}



$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":
        break;
    case "POST":
        

        break;
    case "PUT":

        if($path[5] == "approved") {
            $response = setApproved($path[4], $conn);
            echo json_encode($response );
            return false;
        }

        if($path[5] == "archived") {
            $response = setArchived($path[4], $conn);
            echo json_encode($response );
            return false;
        } 


        


        break;
    case "DELETE":
        break;


}


?>