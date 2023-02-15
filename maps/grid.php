<?php
include '../headers.php';

include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


function generateID() {
   
    $keyLength = 4;
    $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randStr = substr(str_shuffle($str), 0, $keyLength);
    return $randStr;
}

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":
        $sql = "SELECT * FROM grids";

        $path = explode('/', $_SERVER['REQUEST_URI']);
        
        $response = array();


        if(isset($path[4])) {

            $sql .= " WHERE grid_status = :grid_status";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':grid_status', $path[4]);
            $stmt->execute();
            $grids = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($grids as $key => $value) {
                $response[] = [
                                'x' => (int) $value['grid_x'],
                                'y' => (int) $value['grid_y'],
                                'gridParent' => (int) $value['grid_parent'],
                                'gridStatus' => (int) $value['grid_status']
                            ];
            
            }

        } else {

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $grids = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($grids as $key => $value) {
                $response[] = [
                                'x' => (int) $value['grid_x'],
                                'y' => (int) $value['grid_y'],
                                'gridParent' => (int) $value['grid_parent'],
                                'gridStatus' => (int) $value['grid_status']
                            ];
            
            }
        }
        
        echo json_encode($response);


    break;
    case "POST":
        $input = json_decode( file_get_contents('php://input') );
    

        $sql = "INSERT INTO grids(plot_id, grid_x, grid_y, grid_index, grid_parent, grid_status)VALUES(:plot_id, :grid_x, :grid_y, :grid_index, :grid_parent, :grid_status)";


        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':plot_id', $input->plot_id, PDO::PARAM_STR);
        $stmt->bindParam(':grid_x', $input->x, PDO::PARAM_INT);
        $stmt->bindParam(':grid_y', $input->y, PDO::PARAM_INT);
        $stmt->bindParam(':grid_index', $input->gridIndex, PDO::PARAM_INT);
        $stmt->bindParam(':grid_parent', $input->gridParent, PDO::PARAM_INT);
        $stmt->bindParam(':grid_status', $input->gridStatus, PDO::PARAM_INT);


        if($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record created successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to create record.'];
        }


        echo json_encode($response);
        break;

    case "DELETE":

    break;
    case "PUT":

    break;

}