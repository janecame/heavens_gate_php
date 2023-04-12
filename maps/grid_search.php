<?php

include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $path = explode('/', $_SERVER['REQUEST_URI']);
        
        $sql = "SELECT lat, lng FROM plots WHERE plot_id = :seached";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':seached', $path[4]);
    
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            $response = $result;
        }else{
            $response = ['status' => 0, 'message' => 'Sorry, This plot is unknown.'];
        }
        echo json_encode($response);

        break;
    case "POST":

        $input = json_decode( file_get_contents('php://input'));
        
        $sql = "SELECT orders.plot_id, orders.status, orders.order_id, personal.lastname, personal.firstname, personal.middlename
                FROM orders
                JOIN personal
                ON orders.order_id = personal.order_id
                WHERE orders.plot_id = :plot_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':plot_id', $input->plot_id, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($result) {
            $response = ['status' => 1, 'message' => 'plot retrived successfully', 'data' => $result];
        }else{
            $response = ['status' => 0, 'message' => 'plot retrived failed'];
        }
       
        echo json_encode($response);
        break;

    case "PUT":

        break;

    case "DELETE":



        break;
}

?>