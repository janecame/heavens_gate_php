<?php

include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $path = explode('/', $_SERVER['REQUEST_URI']);
        
         
         if ($path[4] === "types") {

            $id = $path[5] + 1;

            $sql = "SELECT plot_type FROM plot_type WHERE id = :id";

            $stmt = $conn->prepare($sql); 
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $response = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($response);

         }else{
            $sql = "SELECT plots.plot_type AS plotTypeID, plots.plot_id, plot_type.plot_type, plots.buyer_id
                FROM plots
                JOIN plot_type
                ON plots.plot_type = plot_type.id
                WHERE plots.plot_id = :plot_id";

            $stmt = $conn->prepare($sql); 
            $stmt->bindParam(':plot_id', $path[4]);
            $stmt->execute();
            $response = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($response);
         }


        
        
       
       

        break;
    case "POST":

    

    case "PUT":

        break;

    case "DELETE":



        break;
}

?>