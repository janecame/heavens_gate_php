<?php 


include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //add error handling

function getName($id, $conn){
    
    if ($id) {
        $query = "SELECT firstname, lastname FROM `users` WHERE `user_id` = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $id);
        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        return $info['lastname'] . ', ' . $info['firstname'];
    }else{
        return "";
    }
   
}

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":
        $path = explode('/', $_SERVER['REQUEST_URI']);
        $sql = "SELECT * FROM payments ";
        $sql .= "WHERE plot_id = :plot_id ORDER BY datetime_received DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':plot_id', $path[4]);

        try {
            $stmt->execute();
            $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = array();

            foreach ($list as $key => $value) {
                $response[] = [
                                'datetime_sent' => $value['datetime_sent'],
                                'datetime_received' => $value['datetime_received'],
                                'id' => $value['id'],
                                'official_receipt' => $value['official_receipt'],
                                'sender' => getName($value['sender'], $conn),
                                'status' => $value['status'],
                                'value' => $value['amount'],
                                'receiver' => getName($value['receiver'], $conn),
                              ];
            }

            echo json_encode($response);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage(); // add error handling
        }

        break;

    case "POST":
        // add code for POST request

        break;

    case "PUT":
        // add code for PUT request

        break;

    case "DELETE":
        // add code for DELETE request

        break;
}


?>