

<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

function getNumberOfPlots($client_id, $conn){
   
    $query = "SELECT count(id) AS numberOfPlots FROM `clients` WHERE `client_id` = :client_id";
    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':client_id', $client_id);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['numberOfPlots'];

}

function getStatus($status){
    if ($status == 0)  return "Unverified";
    if ($status == 1)  return "Verified";
    if ($status == 2)  return "Archive";
}

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

    	$sql = "SELECT * FROM users";



        $path = explode('/', $_SERVER['REQUEST_URI']);

        if(isset($path[4])) {
            $sql .= " WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $path[4]);
            $stmt->execute();
            $response = $stmt->fetch(PDO::FETCH_ASSOC);


        } else {
        	$sql .= " WHERE type = 0";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = array();
            foreach ($orders as $key => $value) {
                $response[] = [
                                'user_id' => $value['user_id'],
                                'name' => $value['firstname'] . " " . $value['middlename'] . " " . $value['lastname'],
                                'verification' => getStatus(intval($value['verification'])),
                                'plot_no' => getNumberOfPlots($value['user_id'], $conn),
                                'date_created' => $value['date_created']
                              ];
            }
            
            
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