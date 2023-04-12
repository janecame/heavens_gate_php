<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();




function getStatus($status){
    if ($status == 0)  return "Pending";
    if ($status == 1)  return "Approved";
    if ($status == 2)  return "Archive";
}


function getCustomerID($username, $conn){

    $query = "SELECT user_id FROM `users` WHERE `username` = :username";
    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $row = $stmt->fetch();
 
    return $row['user_id'];

}



$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $path = explode('/', $_SERVER['REQUEST_URI']);
        $client_id = getCustomerID($path[4], $conn);

    	$sql = "SELECT * FROM clients WHERE client_id = :client_id ";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':client_id', $client_id);
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($response) {
            $response = ['message' => 'Account existed.', 'status' => 1, 'obj' => $response];
            
        } else {
            $response = ['message' => 'No account purchased yet.', 'status' => 0];
    
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