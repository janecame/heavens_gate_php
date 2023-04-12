<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $path = explode('/', $_SERVER['REQUEST_URI']);
        $sql = "SELECT accounts.plot_id, clients.status, clients.reservation, accounts.type, accounts.class, accounts.plan, accounts.years FROM accounts JOIN clients ON accounts.plot_id = clients.plot_id JOIN users ON clients.client_id = users.user_id WHERE users.username = :username";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':username', $path[4]);
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC); 

        if ($response) {
            $response = ['message' => 'Account existed.', 'status' => 1, 'obj' => $response];
            echo json_encode($response);
        } else {
            $response = ['message' => 'No account purchased yet.', 'status' => 0];
            echo json_encode($response);
        }



        break;
    case "POST":
            
        
        break;
    case "PUT":


        break;
    case "DELETE":
        break;


}


?>