<?php

include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

function getName($firstname, $middlename, $lastname){
    return $firstname . " " . substr($middlename, 0, 1) . ". " . $lastname;
}

$sql = "SELECT orders.plot_id, personal.lastname, personal.firstname, personal.middlename
        FROM orders JOIN personal ON orders.order_id = personal.order_id";
        
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = array();
foreach ($result as $key => $value) {
    $response[] = [
                    'plot_id' => $value['plot_id'],
                    'name' => getName($value['firstname'], $value['middlename'], $value['lastname'])  
                ];
        
}

echo json_encode($response);

?>