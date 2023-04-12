<?php

include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();



function getPlotData($conn, $plot_id) {
    $sql = "SELECT orders.plot_id, orders.status, orders.order_id, personal.lastname, personal.firstname, personal.middlename
            FROM orders
            JOIN personal
            ON orders.order_id = personal.order_id
            WHERE orders.plot_id = :plot_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':plot_id', $plot_id, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($result) {
        

       return $response = ['status' => 1, 'message' => 'plot exist', 'data' => $result];
    }else{
       return $response = ['status' => 2, 'message' => 'plot available', 'data' => $plot_id];
    }
}   


function gridCheck($path, $conn, $result) {
  switch ($path) {
    case "admin":
        if ($result) {
            $response = getPlotData($conn, $result['plot_id']);
        } else {
            $response = ['status' => 0, 'message' => 'plot unknown'];
        }

        return  $response;

      break;
    case "member":

        /*if ($result) {
            $response = ['status' => 1, 'message' => 'exist', 'plot_id' => $result['plot_id']];
        } else {
            $response = ['status' => 0, 'message' => 'does not exist'];
        }
*/
        return  $result;

      break;
    case "guest":

        if ($result['status'] == 1) {
            $response = ['status' => 1, 'message' => 'Plot is Available', 'plot_id' => $result['plot_id']];

        }else if ($result['status'] == 2 || $result['status'] == 3) {
            $response = ['status' => 2, 'message' => 'Plot is Occupied or Sold', 'plot_id' => $result['plot_id']];
        }else if($result['status'] == 0){
            $response = ['status' => 0, 'message' => 'Plot Unavailable', 'plot_id' => $result['id']];
        }else{
            $response = "unknown";
        }
        
        return  $response;
      break;
    default:
      return "path does not exist";
      break;
  }
}


function availabilityCheck($result) {
    if ($result === 1) return $response = ['status' => 1, 'message' => 'plot available'];
    if ($result === 2 || $result === 3) return $response = ['status' => 2, 'message' => 'Sorry, This plot is occupied or sold'];
            

}


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":
        $path = explode('/', $_SERVER['REQUEST_URI']);
        
        $sql = "SELECT * FROM plots WHERE plot_id = :plot_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':plot_id', $path[4]);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $response = availabilityCheck($result['status']);
        }else{
            $response = ['status' => 0, 'message' => 'Sorry, Invalid Plot.'];
        } 
        echo json_encode($response);
        break;


    case "POST":

        $input = json_decode( file_get_contents('php://input'));
        
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $sql = "SELECT * FROM plots WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $input->plot_id);
        // $stmt->bindValue(':lat', $input->lat);
        // $stmt->bindValue(':lng', $input->lng);
        // $stmt->bindValue(':status', $input->status);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            $response = gridCheck($path[4], $conn, $result);
        }else{
            $response = ['status' => 0, 'message' => 'Sorry, This plot is unknown.'];
        }

       
       
        echo json_encode($response);
        break;

    case "PUT":

        break;

    case "DELETE":



        break;
}

?>