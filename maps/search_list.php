<?php

include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

function getName($firstname, $middlename, $lastname){
    return $firstname . " " . substr($middlename, 0, 1) . ". " . $lastname;
}
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y');
}

$sql = "SELECT plot_id, firstname, middlename, lastname, born_on, died_on FROM deceased";
        
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = array();
foreach ($result as $key => $value) {
    $response[] = [
                    'plot_id' => $value['plot_id'],
                    'name' => getName($value['firstname'], $value['middlename'], $value['lastname']),
                    'born_on' =>  formatDate($value['born_on']),
                    'died_on' =>  formatDate($value['died_on'])
                ];
        
}

echo json_encode($response);

?>