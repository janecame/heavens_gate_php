<?php

include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $path = explode('/', $_SERVER['REQUEST_URI']);
        
        $sql = "SELECT grids.plot_id, grids.grid_x, grids.grid_y, grids.grid_parent, grid_parent.rotation, grid_parent.parent_x, grid_parent.parent_y
            FROM grids
            JOIN orders ON grids.plot_id = orders.plot_id
            JOIN grid_parent ON grids.grid_parent = grid_parent.parent_id
            WHERE orders.plot_id = :seached OR orders.order_id = :seached";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':seached', $path[4]);
    
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            $response = [   
                            'rotation' => $result['rotation'],
                            'parent_x' => $result['parent_x'],
                            'parent_y'=> $result['parent_y'],
                            'rect_x' => $result['grid_x'], 
                            'rect_y' => $result['grid_y'],
                            'plot_id' => $result['plot_id']
                        ];
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