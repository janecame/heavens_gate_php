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
        $customer_id = getCustomerID($path[4], $conn);
    	$sql = "SELECT * FROM orders WHERE customer_id = :customer_id ";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = array();
        foreach ($orders as $key => $value) {
            $response[] = [
                            'order_id' => $value['order_id'],
                            'plot_id' => $value['plot_id'],
                            'customer_id' => $value['customer_id'],
                            'status' => getStatus($value['status'])
                          ];
            
            
            
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