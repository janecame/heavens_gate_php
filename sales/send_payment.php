<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

require_once 'paymentAcceptMessage.php';


function filenameid() {
    $date = date("y");
    $keyLength = 8;
    $str = "123456789abcdefg";
    $randStr = substr(str_shuffle($str), 0, $keyLength);
    return $date . '' . $randStr;
}

function receiptID() {
    $date = date("y");
    $keyLength = 12;
    $str = "123456789abcdefg";
    $randStr = substr(str_shuffle($str), 0, $keyLength);
    return $date . '' . $randStr;
}

function hasFiles($files){
    if ($files != null) {
        return 1;
    }else{
        return 0;
    }
}

function photoReceiptUrl($filename){
    
    $dir = 'sales/receipts_img/';
    global $server;
    
    $file = "receipts_img/".$filename.".*";
    $fileinfo = glob($file);

    if (empty($fileinfo)) {
      // If no file was found, return an error or a default value
      return "File not found";
    }

    $filepath = $fileinfo[0];
    $fileext = explode(".", $filepath);
    $fileactualext = $fileext[count($fileext) - 1];
    $image_url = $server . $dir . $filename . ".".$fileactualext;

    return $image_url;

}

function myType($type){
    switch ($type) {
        case 0:
            return "Reservation";
            break;
        case 1:
            return "Down Payment";
            break;
        case 2:
            return "Monthly Payment";
        case 3:
            return "Cash";
            break;
        default:
            return "unknown";

    }
}

function getMethod($method){
    switch ($method) {
        case 0:
            return "G-Cash";
            break;
        case 1:
            return "Bank BPI";
            break;
        case 2:
            return "Cash";
            break;
        default:
            return "unknown";

    }
}

function getStatus($status){

    switch ($status) {
        case 0:
            return "Pending";
            break;
        case 1:
            return "Received";
            break;
        default:
            return "unknown";

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



function getPlotPlanInfo($id, $conn){

    $query = "SELECT * FROM `accounts` WHERE `plot_id` = :plot_id";
    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
 
    return $info;
}

function getTotalBalance($id, $conn){

    $query = "SELECT SUM(payments.amount) AS totalAmountPaid, accounts.lot_price
              FROM payments
              INNER JOIN accounts
              ON payments.plot_id = accounts.plot_id
              WHERE payments.plot_id = :plot_id";

    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':plot_id', $id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $listPrice = $result['lot_price'] * 0.15 + $result['lot_price'];
    $totalBalance = $listPrice - $result['totalAmountPaid'];

    return $totalBalance;


}


function getMonthlyPayment($id, $conn) {
  
    $query = "SELECT payments.datetime_received, accounts.years, 
              FROM payments
              INNER JOIN accounts
              ON payments.plot_id = accounts.plot_id
              WHERE payments.plot_id = :plot_id AND payments.type = 1";

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

    
    return $result['numberOfReceives'];
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


function getUserID($username, $conn){
   
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

        $sql = "SELECT * FROM payments ";

        $path = explode('/', $_SERVER['REQUEST_URI']);

        if(isset($path[4])) {
            $sql .= " WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $path[4]);
            $stmt->execute();
            $info = $stmt->fetch(PDO::FETCH_ASSOC);


            $response = [
                        ...$info, 
                        'img_url' => photoReceiptUrl($info['filename']),
                        'accounts' => getPlotPlanInfo($info['plot_id'], $conn),
                        'totalBalance' => getTotalBalance($info['plot_id'], $conn),
                        'monthlyDates' => getMonthlyPaymentDates($info['plot_id'], $conn),
                        'numberOfReceives' => getMonthlyPaymentReceives($info['plot_id'], $conn),
                        'remainingMonths' => getRemainingMonths($info['plot_id'], $conn)
                    ];


        } else {
            $sql .= " ORDER BY datetime_sent DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = array();

            foreach ($list as $key => $value) {
                $response[] = [
                                'datetime_sent' => $value['datetime_sent'],
                                'datetime_received' => $value['datetime_received'],
                                'id' => $value['id'],
                                'official_receipt' => $value['official_receipt'],
                                'sender' => $value['sender'],
                                'plot_id' => $value['plot_id'],
                                'method' => getMethod($value['method']),
                                'status' => getStatus($value['status']),
                                'type' => myType($value['type']),
                                'typeRender' => $value['type'],
                                'amount' => $value['amount']
                              ];
            }
        }

        echo json_encode($response);


        break;
    case "POST":
            
            $path = explode('/', $_SERVER['REQUEST_URI']);


            if ($path[4] === 'oncash') {
               

                $plot_id = $_POST['plot_id'];
                $sender = $_POST['sender'];
                $method = $_POST['method'];


                $type = $_POST['type'];

                $sql = "INSERT INTO payments(plot_id, sender, type, method, datetime_sent) VALUES(:plot_id, :sender, :type, :method, NOW())";
                    
                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':plot_id', $plot_id);
                $stmt->bindParam(':sender', $sender);
                $stmt->bindParam(':method', $method);
                $stmt->bindParam(':type', $type);

                
                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Payment Sent Successfully .'];
                }else{
                    $response = ['status' => 0, 'message' => 'Failed to send.'];   
                }


            }else{

                $refference = $_POST['refference'];
                $file = $_FILES["file"];            
                $plot_id = $_POST['plot_id'];
                $sender = $_POST['sender'];
                $method = $_POST['method'];
                $fileStat = hasFiles($file);

                $type = $_POST['type'];
                
                $filenameid = filenameid();
                
                if (hasFiles($file) === 0) {
                    $response = ['status' => 0, 'message' => 'Failed to upload.'];
                    echo json_encode($response);
                    return false;
                }   
               
                $fileNameNew = $filenameid."."."jpg";
                move_uploaded_file($_FILES["file"]["tmp_name"], "receipts_img/" . $fileNameNew);

                $sql = "INSERT INTO payments(plot_id, sender, refference_no, photo_receipt, type, method, filename, datetime_sent) VALUES(:plot_id, :sender, :refference_no, :receipt_img, :type, :method, :filename, NOW());

                    UPDATE clients SET reservation = 0 WHERE plot_id = :plot_id;

                    ";
                    
                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':filename', $filenameid);
                $stmt->bindParam(':plot_id', $plot_id);
                $stmt->bindParam(':sender', $sender);
                $stmt->bindParam(':method', $method);
                $stmt->bindParam(':refference_no', $refference, PDO::PARAM_STR); 
                $stmt->bindParam(':receipt_img', $fileStat);
                $stmt->bindParam(':type', $type);
                
                if($stmt->execute()) {
                    $response = [
                                'status' => 1, 
                                'message' => 'Payment Sent Successfully.',

                            ];
                }else{
                    $response = ['status' => 0, 'message' => 'Failed to send.'];   
                }

            }

            echo json_encode($response);
        
        break;
    case "PUT":

        $data = json_decode(file_get_contents('php://input'));
        $receipt_id = receiptID();
        $receiver = getUserID($data->receiver, $conn);
        $status = 1;

       
        if ($data->type === 0) {
            $sql = "UPDATE clients 
                    SET reservation = 2 
                    WHERE plot_id = (SELECT plot_id FROM payments WHERE id = :id)";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $data->id);
            $stmt->execute();
        }        

        $sql = "UPDATE payments SET datetime_received = NOW(), status = :status, official_receipt = :official_receipt, amount = :amount, receiver = :receiver WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':official_receipt', $receipt_id);
        $stmt->bindParam(':id', $data->id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':receiver', $receiver);
        $stmt->bindParam(':amount', $data->amount);

        if($stmt->execute()) {
            $paymentInfo = getPaymentInfo($data->id, $data->amount, $conn);
            $response = ['status' => 1, 'message' => $paymentInfo];
        } else {
            $response = ['status' => 0, 'message' => 'Payment Accept Failed.'];
        }
        echo json_encode($response);

       


        break;
    case "DELETE":
        break;


}


?>