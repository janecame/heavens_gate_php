<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();



function getPlan($val){

    switch ($val) {
        case 0:
            return 0.24;
            break;
        case 1:
            return 0.15;
            break;
        case 2:
            return 0;
            break;
        default:
            return 0;

    }
}

function getReservationExpiration($id, $conn) {

    $query = "SELECT datetime_received, status
                  FROM payments
                  WHERE plot_id = :plot_id AND type = 0"; 

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $currentDate = new DateTime($result['datetime_received']);
    $daysToAdd = 15;
    $interval = new DateInterval('P' . $daysToAdd . 'D');
    $targetDate = $currentDate->add($interval);
    
    return $targetDate->format('F j, Y');
}

function getPayment($id, $conn){

    $payment = 0;

    $query = "SELECT accounts.lot_price, accounts.plan, accounts.years
              FROM payments
              INNER JOIN accounts
              ON payments.plot_id = accounts.plot_id
              WHERE payments.plot_id = :plot_id";

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['years'] === 0) {
        $payment = $result['lot_price'];
    }else{
        $listPrice = $result['lot_price'] * 0.15 + $result['lot_price'];
        $payment = $listPrice * getPlan(intval($result['plan']));
    }
    
    return $payment;
}


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":


        $response = array();
        $path = explode('/', $_SERVER['REQUEST_URI']);

        if(isset($path[4])) {

            $response = [
                            'payment' => getPayment($path[4], $conn),
                            'expiration' => getReservationExpiration($path[4], $conn)
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