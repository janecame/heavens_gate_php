<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


function getUserID($username, $conn){
   
    $query = "SELECT user_id FROM `users` WHERE `username` = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    return $info['user_id'];

}

function getStatus($status){
    if ($status == 0)  return "Pending";
    if ($status == 1)  return "Approved";
    if ($status == 2)  return "Archive";
}


function getImageURL($client_id, $type){
    
    $dir = 'customer/uploads/';
    //$server = 'http://localhost/heavens_gate/';
    global $server;

    $filename = "../customer/uploads/".$type."-".$client_id.".*";
    $fileinfo = glob($filename);

    if (empty($fileinfo)) {
      // If no file was found, return an error or a default value
      return "File not found";
    }

    $filepath = $fileinfo[0];
    $fileext = explode(".", $filepath);
    $fileactualext = $fileext[count($fileext) - 1];
    $image_url = $server . $dir . "".$type."-".$client_id.".".$fileactualext;

    return $image_url;

}

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

    	$sql = "SELECT * FROM clients";
        
        $path = explode('/', $_SERVER['REQUEST_URI']);

        if($path[4] === "fetchAll") {

            $user_id = getUserID($path[5], $conn);
            
            $sql .= " WHERE client_type = 2 && broker_id = :broker_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':broker_id', $user_id);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = array();
            foreach ($orders as $key => $value) {
                $response[] = [
                                'plot_id' => $value['plot_id'],
                                'client_id' => $value['client_id'],
                                'statusMessage' => getStatus($value['status']),
                                'name' => $value['firstname'] . " " . $value['middlename'] . " " . $value['lastname'],
                                
                                'active' => 1,
                                'status' => $value['status']


                              ];
            }

        } else {
        	$sql .= " WHERE plot_id = :plot_id AND client_type = 2";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':plot_id', $path[4]);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $response = $result;
            $response['imgID'] = getImageURL($result['client_id'], "id");
            $response['imgReceipt'] = getImageURL($result['client_id'], "receipt");
            $response['imgSpecimen'] = getImageURL($result['client_id'], "specimen");
            $response['imgESign'] = getImageURL($result['client_id'], "eSign");
            
            
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