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

function getNumberOfMonths($years){

    switch ($years) {
        case 1:
            return 12;
            break;
        case 2:
            return 24;
            break;
        case 3:
            return 36;
            break;
        default:
            return 0;

    }
}


function isPlan($id, $conn){
    $query = "SELECT years FROM `accounts` WHERE `plot_id` = :plot_id";
    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    

    return intval($info['years']);

}

function getPlotPlanInfo($id, $conn){

    $query = "SELECT * FROM `accounts` WHERE `plot_id` = :plot_id";
    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
 
    return $info;
}

function getTotalAmountPaid($id, $conn){

    $query = "SELECT SUM(payments.amount) AS totalAmountPaid
              FROM payments
              INNER JOIN accounts
              ON payments.plot_id = accounts.plot_id
              WHERE payments.plot_id = :plot_id";

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalAmountPaid = $result['totalAmountPaid'];

    return $totalAmountPaid;


}

function getTotalBalance($id, $conn){

    $totalBalance = 0;

    $query = "SELECT SUM(payments.amount) AS totalAmountPaid, accounts.lot_price, accounts.plan, accounts.years
              FROM payments
              INNER JOIN accounts
              ON payments.plot_id = accounts.plot_id
              WHERE payments.plot_id = :plot_id";

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    

    if ($result['years'] === 0) {
        $lotPrice = $result['lot_price'];
        $totalBalance = $lotPrice - $result['totalAmountPaid'];
    }else{
        $listPrice = $result['lot_price'] * 0.15 + $result['lot_price'];
        $totalBalance = $listPrice - $result['totalAmountPaid'];
    }

    

    return $totalBalance;


}


function getDownPayment($id, $conn){

     $query = "SELECT accounts.lot_price, accounts.plan
              FROM payments
              INNER JOIN accounts
              ON payments.plot_id = accounts.plot_id
              WHERE payments.plot_id = :plot_id";

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $listPrice = $result['lot_price'] * 0.15 + $result['lot_price'];

    $downPayment = $listPrice * getPlan(intval($result['plan']));

    return $downPayment;
    
}

function getLotPrice($id, $conn){

     $query = "SELECT lot_price
              FROM accounts
              WHERE plot_id = :plot_id";

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['lot_price'] ;
    
}

function getMonthlyPayment($id, $conn) {
  
    $query = "SELECT payments.datetime_received, accounts.years, 
              FROM payments
              INNER JOIN accounts
              ON payments.plot_id = accounts.plot_id
              WHERE payments.plot_id = :plot_id AND payments.type = 1"; //AND payments.type = 1

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $numYears = $result['years'];
    $startDate = $result['datetime_received'];
  
    $payment_timestamp = strtotime($startDate);
    $payment_date_only = date('Y-m-d', $payment_timestamp);
      
    $monthlyDates = array();
      
      // Calculate the starting month and year
    $startMonth = (int)date('m', strtotime($payment_date_only));
    $startYear = (int)date('Y', strtotime($payment_date_only));
      
      // Calculate the total number of months
    $numMonths = $numYears * 12;
      
      // Loop through each month and calculate the payment date
    for ($i = 0; $i < $numMonths; $i++) {
        $paymentMonth = $startMonth + $i;
        $paymentYear = $startYear + floor($paymentMonth / 12);
        $paymentMonth = ($paymentMonth % 12) + 1;
        $paymentDate = sprintf("%04d-%02d-%02d", $paymentYear, $paymentMonth, 5);
        array_push($monthlyDates, $paymentDate);
    }
      
    return $monthlyDates;
}

function getMonthlyPaymentReceives($id, $conn) {
  
    $query = "SELECT 
              COUNT(*) AS numberOfReceives 
              FROM payments 
              WHERE plot_id = :plot_id AND status = 1 AND type = 2";

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    
    return intval($result['numberOfReceives']);
}


function getRemainingMonths($id, $conn) {
  
    $query = "SELECT years
              FROM accounts  
              WHERE plot_id = :plot_id";

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $months = getNumberOfMonths($result['years']);
    $monthsPaid = getMonthlyPaymentReceives($id, $conn);

    $remainingMonths = $months - $monthsPaid;
    
    return $remainingMonths;
}

function getMonthlyPaymentDates($id, $conn) {
  
    $query = "SELECT payments.datetime_received, accounts.years 
              FROM payments
              INNER JOIN accounts
              ON payments.plot_id = accounts.plot_id
              WHERE payments.plot_id = :plot_id AND payments.type = 1";

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($result === false) {
        return false;
    }
    $numYears = $result['years'];
    $startDate = $result['datetime_received'];
  
    $payment_timestamp = strtotime($startDate);
    $payment_date_only = date('Y-m-d', $payment_timestamp);
      
    $monthlyDates = array();
      
      // Calculate the starting month and year
    $startMonth = (int)date('m', strtotime($payment_date_only));
    $startYear = (int)date('Y', strtotime($payment_date_only));
      
      // Calculate the total number of months
    $numMonths = $numYears * 12;
      
      // Loop through each month and calculate the payment date
    for ($i = 0; $i < $numMonths; $i++) {
        $paymentMonth = $startMonth + $i;
        $paymentYear = $startYear + floor($paymentMonth / 12);
        $paymentMonth = ($paymentMonth % 12) + 1;
        $paymentDate = sprintf("%04d-%02d-%02d", $paymentYear, $paymentMonth, 5);
        array_push($monthlyDates, $paymentDate);
    }
      
    return $monthlyDates;
}



$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":


        $response = array();
        $path = explode('/', $_SERVER['REQUEST_URI']);

        if(isset($path[4])) {


            //isPlan($path[4], $conn)
            if (isPlan($path[4], $conn) === 0) {

                $response = [
                        'accounts' => getPlotPlanInfo($path[4], $conn),
                        'totalBalance' => getTotalBalance($path[4], $conn),
                        'monthlyDates' => 0,
                        'numberOfReceives' => 0,
                        'remainingMonths' => 0,
                        'downPayment' => 0,
                        'monthlyDues' => 0,
                        'cash' => true
                        ];
                // code...
            }else{
                $response = [
                        'accounts' => getPlotPlanInfo($path[4], $conn),
                        'totalBalance' => getTotalBalance($path[4], $conn),
                        'monthlyDates' => getMonthlyPaymentDates($path[4], $conn),
                        'numberOfReceives' => getMonthlyPaymentReceives($path[4], $conn),
                        'remainingMonths' => getRemainingMonths($path[4], $conn),
                        'downPayment' => getDownPayment($path[4], $conn),
                        'monthlyDues' => getTotalBalance($path[4], $conn) / getRemainingMonths($path[4], $conn),
                        'totalAmountPaid' => getTotalAmountPaid($path[4], $conn),
                        'cash' => false
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