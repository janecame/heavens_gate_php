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

function getESignFilename($user_id, $conn){
    if ($user_id === "") { 
        return "Not yet reviewed by sales";
    }
    $query = "SELECT eSign FROM `users` WHERE `user_id` = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    return $info['eSign'];

}

function getName($user_id, $conn){
   
    if ($user_id === "") { 
        return "Not yet reviewed by sales";
    }else{
        $query = "SELECT firstname, lastname FROM `users` WHERE `user_id` = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        return $info['lastname'] . ', ' . $info['firstname'];
    }

    

}

function getImageURL($client_id, $type){
    
    $dir = 'customer/uploads/';
    //$server = 'http://localhost/heavens_gate/';
    global $server;

    $filename = "../customer/uploads/$client_id/files/".$type."-".$client_id.".*";
    $fileinfo = glob($filename);

    if (empty($fileinfo)) {
      // If no file was found, return an error or a default value
      return "File not found";
    }

    $filepath = $fileinfo[0];
    $fileext = explode(".", $filepath);
    $fileactualext = $fileext[count($fileext) - 1];
    $image_url = $server . $dir . "$client_id/files/".$type."-".$client_id.".".$fileactualext;

    return $image_url;

}


function getESign($consultantID, $conn){
    
    $dir = 'profiles/';
    $server = 'http://localhost/heavens_gate/';

    if ($consultantID === "") { 
        return "Not yet reviewed by sales";
    }
    $name = getESignFilename($consultantID, $conn);

    $filename = "../profiles/esign-" . $name .".*";
    $fileinfo = glob($filename);

    if (empty($fileinfo)) {
       return 0;
    }

    $filepath = $fileinfo[0];
    $fileext = explode(".", $filepath);
    $fileactualext = $fileext[count($fileext) - 1];
    $image_url = $server . $dir . "esign-" . $name . "." . $fileactualext;  

    return $image_url;
}


function isTrue($num) {
  if ($num === 1) {
    return true;
  } else {
    return false;
  }
}

function getPlanDetails($plot_id, $conn){

    $sql = "SELECT accounts.lot_price AS lotPrice, accounts.plan, accounts.type, accounts.class,  accounts.years 
            FROM clients
            JOIN accounts
            ON clients.plot_id = accounts.plot_id
            WHERE clients.plot_id = :plot_id";



    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':plot_id', $plot_id);

    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_ASSOC);


    return $product;

}

function getPayments($plot_id, $conn){

    $status = 1;

    $sql = "SELECT * FROM payments ";
    $sql .= "WHERE plot_id = :plot_id AND status = :status ORDER BY datetime_received DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':plot_id', $plot_id);
    $stmt->bindParam(':status', $status);

    try {
        $stmt->execute();
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = array();

        foreach ($list as $key => $value) {
            $response[] = [ 
                            'datetime_received' => $value['datetime_received'],
                            'official_receipt' => $value['official_receipt'],
                            'amount' => $value['amount'],
                          ];
        }

        return $response;
    } catch(PDOException $e) {
        return "Error: " . $e->getMessage(); // add error handling
    }
}

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

    	$sql = "SELECT * FROM clients";

        $path = explode('/', $_SERVER['REQUEST_URI']);

        if(isset($path[4])) {

            $sql .= " WHERE plot_id = :plot_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':plot_id', $path[4]);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $response = $result;

            $response['nameofspouse'] = $result['co_owner'];
            $response['civilstatus'] = $result['civil_status'];

            $response['payments'] = getPayments($result['plot_id'], $conn);
            $response['plot'] = $result['plot_id'];

            $response['lay_away_memorial_services'] = isTrue($result['memorial_services']);
            $response['lay_away_interment'] = isTrue($result['interment']);
            $response['lay_away_cremation'] = isTrue($result['cremation']);
            $response['type_memorial_lot'] = isTrue($result['memorial_lot']);
            $response['type_crypt'] = isTrue($result['crypt']);
            $response['type_lay_away'] = isTrue($result['lay_away']);
            $response['type_bundles'] = isTrue($result['bundles']);
            $response['plan'] = getPlanDetails($result['plot_id'], $conn);

            $response['imgID'] = getImageURL($result['client_id'], "id");
            $response['imgSpecimen'] = getImageURL($result['client_id'], "specimen");
            $response['eSignOwner'] = getImageURL($result['client_id'], "eSignOwner");
            $response['eSignSpouse'] = getImageURL($result['client_id'], "eSignSpouse");

            $response['eSignConsultant'] = getESign($result['consultantID'], $conn);
            $response['consultant'] = getName($result['consultantID'], $conn);

        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = array();
            foreach ($orders as $key => $value) {
                $response[] = [
                                'plot_id' => $value['plot_id'],
                                'client_id' => $value['client_id'],
                                'statusMessage' => getStatus($value['status']),
                                'name' => $value['firstname'] . " " . $value['middlename'] . " " . $value['lastname'],
                                'order_date' => "12/12/2000",
                                'due' => 1500,
                                'active' => 1,
                                'status' => $value['status']


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