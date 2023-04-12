<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();




function getPaymentType($id, $conn){

   

    $query = "SELECT reservation
              FROM clients
              WHERE plot_id = :plot_id";

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['reservation'];
}


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $path = explode('/', $_SERVER['REQUEST_URI']);

        $response = 0;
        
        if(isset($path[4])) {

            $query = "SELECT reservation
              FROM clients
              WHERE plot_id = :plot_id";

            $stmt = $conn->prepare($query);        
            $stmt->bindParam(':plot_id', $path[4]);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            //return $result['reservation'];
            if ($result['reservation'] === 2) {
                $response = 1;
            }else{
                $response = 2;
            }
           
        }

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