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

/*function generateFileName($client_id) {
    $date = date("y");
    $keyLength = 8;
    $str = "12345678abcdefghijklmnopqrstuvwxyz";
    $randStr = substr(str_shuffle($str), 0, $keyLength);

    return $date . '' . $client_id . '' . $randStr;
}*/



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
   
    $query = "SELECT user_id, type FROM `users` WHERE `username` = :username";
    $stmt = $conn->prepare($query);        
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $row = $stmt->fetch();

    $result = array();

    if ($row['type'] === 2) {
        $result = [
                    'client_id' => generateUserID(), 
                    'broker_id' => $row['user_id'],
                    'type' => $row['type']
                ];
        
    }else{
        $result = [
                    'client_id' => $row['user_id'], 
                    'broker_id' => "",
                    'type' => $row['type']
                ]; 
    }
 
    return $result;

}

function getName($username, $conn){
   
    $query = "SELECT firstname, lastname FROM `users` WHERE `username` = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    return $info['lastname'] . ', ' . $info['firstname'];

}



function filenameid() {
    $date = date("y");
    $keyLength = 8;
    $str = "123456789abcdefg";
    $randStr = substr(str_shuffle($str), 0, $keyLength);
    return $date . '' . $randStr;
}



$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":
        break;
    case "POST":

            $resUser = getCustomerID($_POST['customer'], $conn);

            $client_id = $resUser['client_id'];
            $broker_id = $resUser['broker_id'];
            $client_type = $resUser['type'];
            
            $date_submitted = date('Y-m-d');

            $lastname = $_POST['lastname'];
            $firstname = $_POST['firstname'];
            $middlename = $_POST['middlename'];
            $birthdate = $_POST['birthdate'];
            $age = $_POST['age'];

            $sex = string_to_int($_POST['sex']);
            $civilstatus = string_to_int($_POST['civilstatus']);
            $home_address = $_POST['home_address'];
            $business_address = $_POST['business_address'];
            $office_landline = $_POST['office_landline'];


            


            $email = $_POST['email'];
            $landline = $_POST['landline'];
            $contact = $_POST['contact'];

            $spouseowners = $_POST['nameofspouse'];

            $plot = $_POST['plot'];

            $type_memorial_lot = bool_to_int($_POST['type_memorial_lot']);
            $type_lay_away = bool_to_int($_POST['type_lay_away']);            
            $type_crypt = bool_to_int($_POST['type_crypt']);
            $type_bundles = bool_to_int($_POST['type_bundles']);

            $lay_away_interment = bool_to_int($_POST['lay_away_interment']);            
            $lay_away_cremation = bool_to_int($_POST['lay_away_cremation']);
            $lay_away_memorial_services = bool_to_int($_POST['lay_away_memorial_services']);
            

            $payment_method = $_POST['payment_method'];           
            $refference_no = $_POST['refference_no'];

            $price = $_POST['lotPrice'];           
            $plan = $_POST['plan'];
            $years = $_POST['years'];           
            $class = $_POST['class'];
            $type = $_POST['type'];

            $buying_type = $_POST['buyingType'];

            $paymentType = 0;

            if (intval($buying_type) === 1) {
                if (intval($plan) === 3 || intval($plan) === 4) {
                    $paymentType = 3; //cash
                }else{
                    $paymentType = 1; //down
                }
            }else{
                $paymentType = 0; //reserve
            }

            $filenameid = filenameid();

            $file1 = ['type' => 'receipt', 'file' => $_FILES['photo_receipt']];
            $file2 = ['type' => 'id', 'file' => $_FILES['valid_id_card']];
            $file3 = ['type' => 'specimen', 'file' => $_FILES['three_specimen_signature']];
            $file4 = ['type' => 'eSignSpouse', 'file' => $_FILES['eSignSpouse']];
            $file5 = ['type' => 'eSignOwner', 'file' => $_FILES['eSignOwner']];


            $files = [$file2, $file3, $file4, $file5];

            // $valid_id_img = hasFiles($file2['file']);
            // $three_specimen_img = hasFiles($file3['file']);
            $receipt_img = hasFiles($file1['file']);
            
            // $eSignSpouse = hasFiles($file4['file']);
            // $eSignOwner = hasFiles($file5['file']);


            
            if ($receipt_img === 1) {
                $fileNameNew = $filenameid."."."jpg";
                move_uploaded_file($file1['file']["tmp_name"], "../sales/receipts_img/" . $fileNameNew);
            }


                 // set the directory name based on user id
            $directoryName = "uploads/" . $client_id . "/files";
            // create the directory if it doesn't exist
            if (!file_exists($directoryName)) {
                mkdir($directoryName, 0777, true);
            }

            // loop through the files and upload them to the new directory
            foreach ($files as $file) {
                $fileName = $file['type'] . "-" . $client_id . "." . pathinfo($file['file']['name'], PATHINFO_EXTENSION);
                move_uploaded_file($file['file']['tmp_name'], $directoryName . "/" . $fileName);
            }
            
            

           

            


           $sql = "INSERT INTO clients(plot_id, client_id, broker_id, client_type, lastname, firstname, middlename, birthdate, age, sex, civil_status, home_address, business_address, office_landline, email, landline, contact, co_owner, memorial_lot, lay_away, bundles, interment, crypt, cremation, memorial_services, reservation) VALUES(:plot, :client_id, :broker_id, :client_type, :lastname, :firstname, :middlename, :birthdate, :age, :sex, :civil_status, :home_address, :business_address, :office_landline, :email, :landline, :contact, :name, :memorial_lot, :lay_away, :bundles, :interment, :crypt, :cremation, :memorial_services, :buying_type);

            INSERT INTO accounts(plot_id, owner_id, lot_price, class, years, plan, type) VALUES(:plot, :client_id, :price, :class, :years, :plan, :product);

            INSERT INTO payments(plot_id, sender, refference_no, photo_receipt, type, method, filename, datetime_sent) VALUES(:plot, :client_id, :refference_no, :receipt_img, :payment_type, :payment_method, :filename, NOW());
                    

                UPDATE plots SET status = 2, buyer_id = :client_id WHERE plot_id = :plot;
                ";

                
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_STR);
            $stmt->bindParam(':broker_id', $broker_id, PDO::PARAM_STR);
            $stmt->bindParam(':plot', $plot, PDO::PARAM_STR);
            $stmt->bindParam(':client_type', $client_type, PDO::PARAM_STR); 

            $stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);  
            $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);  
            $stmt->bindParam(':middlename', $middlename, PDO::PARAM_STR);  
            $stmt->bindParam(':birthdate', $birthdate);  
            $stmt->bindParam(':age', $age, PDO::PARAM_STR);  
            $stmt->bindParam(':sex', $sex);  
            $stmt->bindParam(':civil_status', $civilstatus);
            $stmt->bindParam(':home_address', $home_address, PDO::PARAM_STR);
            $stmt->bindParam(':business_address', $business_address, PDO::PARAM_STR);    
            $stmt->bindParam(':office_landline', $office_landline, PDO::PARAM_STR);        
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);  
            $stmt->bindParam(':landline', $landline, PDO::PARAM_STR);  
            $stmt->bindParam(':contact', $contact, PDO::PARAM_STR);
 
            $stmt->bindParam(':name', $spouseowners, PDO::PARAM_STR); 
            $stmt->bindParam(':memorial_lot', $type_memorial_lot); 
            $stmt->bindParam(':lay_away', $type_lay_away); 
            $stmt->bindParam(':bundles', $type_bundles);
            $stmt->bindParam(':crypt', $type_crypt);

            $stmt->bindParam(':interment', $lay_away_interment);  
            $stmt->bindParam(':cremation', $lay_away_cremation);
            $stmt->bindParam(':memorial_services', $lay_away_memorial_services);  


            // $stmt->bindParam(':valid_id_img', $valid_id_img); 
            // $stmt->bindParam(':three_specimen_img', $three_specimen_img);
            // $stmt->bindParam(':eSignOwner', $eSignOwner);
            // $stmt->bindParam(':eSignSpouse', $eSignSpouse);

            $stmt->bindParam(':payment_method', $payment_method); 
            $stmt->bindParam(':refference_no', $refference_no, PDO::PARAM_STR);
            $stmt->bindParam(':receipt_img', $receipt_img);
            $stmt->bindParam(':filename', $filenameid);

            
            $stmt->bindParam(':price', $price); 
            $stmt->bindParam(':plan', $plan); 
            $stmt->bindParam(':years', $years); 
            $stmt->bindParam(':class', $class); 
            $stmt->bindParam(':product', $type); 

            $stmt->bindParam(':buying_type', $buying_type); 
            $stmt->bindParam(':payment_type', $paymentType); 
            


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