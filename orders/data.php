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


function getImageURL($order_id, $type){
    
    $dir = 'customer/uploads/';
    $server = 'http://localhost/heavens_gate/';
    

    $filename = "../customer/uploads/".$type."-".$order_id.".*";
    $fileinfo = glob($filename);

    if (empty($fileinfo)) {
      // If no file was found, return an error or a default value
      return "File not found";
    }

    $filepath = $fileinfo[0];
    $fileext = explode(".", $filepath);
    $fileactualext = $fileext[count($fileext) - 1];
    $image_url = $server . $dir . "".$type."-".$order_id.".".$fileactualext;

    return $image_url;

}

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

    	$sql = "SELECT orders.order_id, orders.customer_id, orders.status,
                       personal.lastname, personal.firstname, personal.middlename, personal.birthdate, personal.age, personal.sex, personal.civil_status, personal.email, personal.landline, personal.contact,
                       payment.type, payment.refference_no, payment.receipt_img,
                       signature_id.valid_id_img, signature_id.three_specimen_img,
                       transaction_type.name, transaction_type.memorial_lot, transaction_type.lay_away, transaction_type.bundles, transaction_type.interment, transaction_type.payment_scheme, transaction_type.no_years_pay, transaction_type.sales_agent
                FROM ((((
                orders 
                INNER JOIN personal ON orders.order_id = personal.order_id)
                INNER JOIN payment ON orders.order_id = payment.order_id)
                INNER JOIN signature_id ON orders.order_id = signature_id.order_id)
                INNER JOIN transaction_type ON orders.order_id = transaction_type.order_id)";



        $path = explode('/', $_SERVER['REQUEST_URI']);

        if(isset($path[4])) {
            $sql .= " WHERE orders.order_id = :order_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':order_id', $path[4]);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            $response = [
                        ...$data, 
                        'imgID' => getImageURL($data['order_id'], "id"),
                        'imgReceipt' => getImageURL($data['order_id'], "receipt"),
                        'imgSpecimen' => getImageURL($data['order_id'], "specimen"),
                        'imgESign' => getImageURL($data['order_id'], "eSign")

                    ]; 

        } else {
        	
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = array();
            foreach ($orders as $key => $value) {
                $response[] = [
                                'order_id' => $value['order_id'],
                                'customer_id' => $value['customer_id'],
                                
                                'status' => getStatus($value['status']),
                                'name' => $value['firstname'] . " " . $value['middlename'] . " " . $value['lastname'],
                                'order_date' => "12/12/2000"
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