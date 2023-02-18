<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

function generateUserID() {
    $date = date("y");
    $keyLength = 4;
    $str = "12345678";
    $randStr = substr(str_shuffle($str), 0, $keyLength) . '-' .
               substr(str_shuffle($str), 0, $keyLength);
    
    return $date . '-' . $randStr;
}

function generateOrderID() {
    $date = date("y");
    $keyLength = 4;
    $str = "12345678ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randStr = substr(str_shuffle($str), 0, $keyLength) . '-' .
               substr(str_shuffle($str), 0, $keyLength);
    
    return $date . '-' . $randStr;
}



function string_to_int($str) {
  return intval($str);
}

function bool_to_int($bool) {
  if ($bool == "true") {
      return 1;
  }else{
      return 0;
  }
}

function hasFiles($files){
    if ($files != null) {
        return 1;
    }else{
        return 0;
    }
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
        break;
    case "POST":

            $customer_id = getCustomerID($_POST['customer'], $conn);
            $order_id = generateOrderID();
            $type = "g-cash";

            $file1 = ['type' => 'receipt', 'file' => $_FILES['photo_receipt']];
            $file2 = ['type' => 'id', 'file' => $_FILES['valid_id_card']];
            $file3 = ['type' => 'specimen', 'file' => $_FILES['three_specimen_signature']];
            $file4 = ['type' => 'eSign', 'file' => $_FILES['eSign']];

            $files = [$file1, $file2, $file3, $file4];

            foreach ($files as $file) {
              $fileName = $file['type']."-".$order_id.".".pathinfo($file['file']['name'], PATHINFO_EXTENSION);
              move_uploaded_file($file['file']['tmp_name'], "uploads/" . $fileName);
            }
   

            $sql = "INSERT INTO personal(order_id, lastname, firstname, middlename, birthdate, age, sex, civil_status, address, email, landline, contact) VALUES(:order_id, :lastname, :firstname, :middlename, :birthdate, :age, :sex, :civil_status, :address, :email, :landline, :contact);

                    INSERT INTO orders(order_id, customer_id, plot_id) VALUES(:order_id, :customer_id, :plot);

                    INSERT INTO transaction_type(order_id, name, memorial_lot, lay_away, bundles, interment, payment_scheme, no_years_pay, sales_agent) VALUES(:order_id, :name, :memorial_lot, :lay_away, :bundles, :interment, :payment_scheme, :no_years_pay, :sales_agent);

                    INSERT INTO signature_id(order_id, valid_id_img, three_specimen_img) VALUES(:order_id, :valid_id_img, :three_specimen_img);

                    INSERT INTO payment(order_id, type, refference_no, receipt_img) VALUES(:order_id, :type, :refference_no, :receipt_img);

                    UPDATE grids SET grid_status = 2 WHERE plot_id = :plot;
                ";
                
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_STR);  
            $stmt->bindParam(':plot', $_POST['plot'], PDO::PARAM_STR); 
            $stmt->bindParam(':lastname', $_POST['lastname'], PDO::PARAM_STR);  
            $stmt->bindParam(':firstname', $_POST['firstname'], PDO::PARAM_STR);  
            $stmt->bindParam(':middlename', $_POST['middlename'], PDO::PARAM_STR);  
            $stmt->bindParam(':birthdate', $_POST['birthdate']);  
            $stmt->bindParam(':age', $_POST['age'], PDO::PARAM_STR);  
            $stmt->bindParam(':sex', string_to_int($_POST['sex']));  
            $stmt->bindParam(':civil_status', string_to_int($_POST['civilstatus']));
            $stmt->bindParam(':address', $_POST['address'], PDO::PARAM_STR);    
            $stmt->bindParam(':email', $_POST['email'], PDO::PARAM_STR);  
            $stmt->bindParam(':landline', $_POST['landline'], PDO::PARAM_STR);  
            $stmt->bindParam(':contact', $_POST['contact'], PDO::PARAM_STR);
 
            $stmt->bindParam(':name', $_POST['spouseowners'], PDO::PARAM_STR); 
            $stmt->bindParam(':memorial_lot', bool_to_int($_POST['type_memorial_lot'])); 
            $stmt->bindParam(':lay_away', bool_to_int($_POST['type_lay_away'])); 
            $stmt->bindParam(':bundles', bool_to_int($_POST['type_bundles'])); 
            $stmt->bindParam(':interment', bool_to_int($_POST['type_interment'])); 
            $stmt->bindParam(':payment_scheme', string_to_int($_POST['payment_scheme'])); 
            $stmt->bindParam(':no_years_pay', string_to_int($_POST['no_years_pay'])); 
            $stmt->bindParam(':sales_agent', string_to_int($_POST['sales_agent']));

            $stmt->bindParam(':valid_id_img', hasFiles($file2['file'])); 
            $stmt->bindParam(':three_specimen_img', hasFiles($file3['file']));

            $stmt->bindParam(':type', $type); 
            $stmt->bindParam(':refference_no', $_POST['refference_no'], PDO::PARAM_STR);
            $stmt->bindParam(':receipt_img', hasFiles($file1['file'])); 

            if($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Successfully .'];

            }else{
                $response = ['status' => 0, 'message' => 'Failed to create record.'];   
            }
            
            echo json_encode($response);
            

        
        break;

    case "PUT":

        break;

    case "DELETE":



        break;
}

?>