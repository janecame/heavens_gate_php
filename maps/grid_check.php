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

        if ($result['grid_status'] == 1) {
            $response = ['status' => 1, 'message' => 'plot available', 'plot_id' => $result['plot_id']];

        }else if ($result['grid_status'] == 2 || $result['grid_status'] == 3) {
            $response = ['status' => 2, 'message' => 'plot occupied or sold', 'plot_id' => $result['plot_id']];
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
        
        $sql = "SELECT * FROM grids WHERE plot_id = :plot_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':plot_id', $path[4]);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $response = availabilityCheck($result['grid_status']);
        }else{
            $response = ['status' => 0, 'message' => 'Sorry, Invalid Plot.'];
        } 
        echo json_encode($response);
        break;


    case "POST":

        $input = json_decode( file_get_contents('php://input'));
        


        $path = explode('/', $_SERVER['REQUEST_URI']);

        $sql = "SELECT * FROM grids WHERE grid_x = :x AND grid_y = :y AND grid_parent = :grid_parent AND grid_index = :grid_index";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':x', $input->x);
        $stmt->bindValue(':y', $input->y);
        $stmt->bindValue(':grid_parent', $input->gridParent);
        $stmt->bindValue(':grid_index', $input->gridIndex);
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