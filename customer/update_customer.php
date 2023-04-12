<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();



function getName($username, $conn){
   
    $query = "SELECT firstname, lastname FROM `users` WHERE `username` = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    return $info['lastname'] . ', ' . $info['firstname'];

}

function getUserID($username, $conn){
   
    $query = "SELECT user_id FROM `users` WHERE `username` = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    return $info['user_id'];

}



$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":
        break;      
    case "POST":

        $data = json_decode(file_get_contents('php://input'));

        $plot_id = $data->plot_id;
        $client_id = $data->client_id;
        $consultantUsername = $data->consultant;

        $consultantID = getUserID($consultantUsername, $conn);
        $division = $data->division;
        $tin = $data->tin;
        $branch_manager = $data->branch_manager;
        $division_manager = $data->division_manager;
        $remarks = $data->remarks;
        $status = $data->status;
        $buyersForm = 1;
        
        
        $sql = "UPDATE 
                clients 
                SET
                status = :status, 
                division = :division, 
                division_manager = :division_manager,
                tin = :tin, 
                branch_manager = :branch_manager, 
                remarks = :remarks,
                consultantID = :consultantID,
                buyersForm = :buyersForm  
                WHERE plot_id = :plot_id;
            ";

            
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':division', $division, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':buyersForm', $buyersForm, PDO::PARAM_INT);

        $stmt->bindParam(':division_manager', $division_manager, PDO::PARAM_STR);
        $stmt->bindParam(':tin', $tin, PDO::PARAM_STR); 
        $stmt->bindParam(':branch_manager', $branch_manager, PDO::PARAM_STR); 
        $stmt->bindParam(':remarks', $remarks, PDO::PARAM_STR);
        $stmt->bindParam(':consultantID', $consultantID, PDO::PARAM_STR); 

        $stmt->bindParam(':plot_id', $plot_id, PDO::PARAM_STR); 
        

        if($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Successfully'];
            
        }else{
            $response = ['status' => 0, 'message' => 'Failed to create record.'];   
        }

        echo json_encode($response);

        
        break;

    case "PUT":
        
       

        break;

    case "DELETE":



        break;
}

?>