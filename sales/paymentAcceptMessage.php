<?php 

require_once 'generatePayment.php';

function getPaymentInfo($id, $amount, $conn){
   
    $query = "SELECT * FROM `payments` WHERE `id` = :id";
    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $product = getAccount($row['plot_id'], $conn);
    $response = setNotification($row['type'], $amount, $product);
 
    return $response;

}

function getAccount($plot_id, $conn){
   
    $query = "SELECT * FROM `accounts` WHERE `plot_id` = :plot_id";
    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $plot_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = [
                    'lotPrice' => $row['lot_price'], 
                    'plan' => $row['plan'], 
                    'years' => $row['years']
                ];
 
    return $response;

}


function setNotification($type, $amount, $product) {
    switch ($type) {
        case 0:
             
            if ($amount > 500) {
                return "Reserve Payment Successfully Received";

            } else {
                $remainingBalance = 500 - $amount; 
                return "Payemnt is not enoungh remaining balance" . $remainingBalance;
            }
            break;
        case 1:
            $payment = generatePayment($product);
            
            if ($amount >= $payment['downPayment']) {
                return "Down Payment Successfully Received";
            } else {
                $remainingBalance = $payment['downPayment'] - $amount; 
                return "Payemnt is not enoungh remaining balance " . $remainingBalance;
            }
            break;
        case 2:
            $payment = generatePayment($product);
            
            if ($amount >= $payment['monthlyAmortization']) {
                return "Monthly Payment Successfully Received";
            } else {
                $remainingBalance = $payment['monthlyAmortization'] - $amount; 
                return "Payemnt is not enoungh remaining balance " . $remainingBalance;
            }

            break;
        case 3:
            $payment = generatePayment($product);
            
            if ($amount >= $payment['lotPrice']) {
                return "Cash Payment Successfully Received";
            } else {
                $remainingBalance = $payment['lotPrice'] - $amount; 
                return "Payemnt is not enoungh remaining balance " . $remainingBalance;
            }
            return $payment;
            break;
        default:
            return "The number is not 0, 1, 2, or 3";
            break;
    }
}


?>