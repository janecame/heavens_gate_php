<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

function generateRecieptID() {
    $date = date("y");
    $keyLength = 4;
    $str = "12345678ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randStr = substr(str_shuffle($str), 0, $keyLength) . '-' .
               substr(str_shuffle($str), 0, $keyLength);
    
    return $date . '-' . $randStr;
}

$path = explode('/', $_SERVER['REQUEST_URI']);

$response = [];

function setArchived($id, $conn) {

    $sql = "UPDATE orders SET status = 2 WHERE order_id = :order_id";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':order_id', $id);

    if($stmt->execute()) {
        $response = ['status' => 1, 'message' => 'Archived successfully.', 'order_id' => $id];
    } else {
        $response = ['status' => 0, 'message' => 'Archived Failed.'];
    }
    return $response;

}

function setApproved($id, $conn) {
    
    $response = [];

    $sql = "UPDATE orders SET status = 1 WHERE order_id = :order_id";
    $stmt = $conn->prepare($sql);
    
    $stmt->bindParam(':order_id', $id);
    
    if($stmt->execute()) {
        $response = encodeSale($id, $conn);
    } else {
        $response = ['status' => 0, 'message' => 'Approved Failed'];
    }

    return $response;

}

function setRestore($id, $conn) {
    
    $response = [];

    $sql = "UPDATE orders SET status = 0 WHERE order_id = :order_id";
    $stmt = $conn->prepare($sql);
    
    $stmt->bindParam(':order_id', $id);
    
    if($stmt->execute()) {
        $response = ['status' => 1, 'message' => 'Restored Successfully.', 'order_id' => $id];
    } else {
        $response = ['status' => 0, 'message' => 'Restored Failed'];
    }

    return $response;

}


function encodeSale($id, $conn) {
    

    $date_paid = date('Y-m-d'); 
    $receipt_id = generateRecieptID();
    $monthly_amount = 800;
    $plot_price = 30000;

    $sql = "SELECT customer_id, plot_id FROM orders WHERE order_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_STR);
    $stmt->execute();
    $order = $stmt->fetch();


    $sql = "INSERT INTO sales(date_paid, receipt_id, monthly_amount, plot_price, customer_id, plot_id) VALUES(:date_paid, :receipt_id, :monthly_amount, :plot_price, :customer_id, :plot_id)";    
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':date_paid', $date_paid);
    $stmt->bindParam(':receipt_id', $receipt_id, PDO::PARAM_STR);
    $stmt->bindParam(':monthly_amount', $monthly_amount);
    $stmt->bindParam(':plot_price', $plot_price);
    $stmt->bindParam(':customer_id', $order['customer_id'], PDO::PARAM_STR);
    $stmt->bindParam(':plot_id', $order['plot_id'], PDO::PARAM_STR);

    if($stmt->execute()) {
        $response = ['status' => 1, 'message' => 'Approved successfully.', 'order_id' => $id];

    }else{
        $response = ['status' => 0, 'message' => 'Failed to create record.'];   
    }

    return $response;
    

}

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":
        break;
    case "POST":
        

        break;
    case "PUT":

        if($path[5] == "approved") {
            $response = setApproved($path[4], $conn);

            echo json_encode($response);
            return false;
        }

        if($path[5] == "archived") {
            $response = setArchived($path[4], $conn);
            echo json_encode($response );
            return false;
        }
        if($path[5] == "restore") {
            $response = setRestore($path[4], $conn);
            echo json_encode($response );
            return false;
        }  


    

        break;
    case "DELETE":
        break;


}


?>