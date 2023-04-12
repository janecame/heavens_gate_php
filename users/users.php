<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


function getUserType($val){
    if ($val == 1)  return "Admin";
    if ($val == 2)  return "Broker";
    if ($val == 3)  return "Sales";
}

function generateUserID() {
    $date = date("y");
    $keyLength = 4;
    $str = "12345678";
    $randStr = substr(str_shuffle($str), 0, $keyLength) . '-' .
               substr(str_shuffle($str), 0, $keyLength);
    
    return $date . '-' . $randStr;
}


function getImageURL($profile){
    
    $dir = 'profiles/';
    global $server;

    if ($profile == null) {
        return $image_url = $server . $dir . "user.png";
    }else{

        $filename = "profiles/profile-".$profile.".*";
        $fileinfo = glob($filename);


        if (empty($fileinfo)) {
           return "file not found.";
        }

        $filepath = $fileinfo[0];
        $fileext = explode(".", $filepath);
        $fileactualext = $fileext[count($fileext) - 1];
        $image_url = $server . $dir . "profile-" . $profile . ".".$fileactualext;  

        return $image_url;

    }
}

function getImageID($profile){
    
    $dir = 'verifications/';
    $server = 'http://localhost/heavens_gate/';

    if ($profile == null) {
        return $image_url = $server . $dir . "user.png";
    }else{

        $filename = "../verifications/id-".$profile.".*";
        $fileinfo = glob($filename);


        if (empty($fileinfo)) {
           return "file not found.";
        }

        $filepath = $fileinfo[0];
        $fileext = explode(".", $filepath);
        $fileactualext = $fileext[count($fileext) - 1];
        $image_url = $server . $dir . "id-" . $profile . ".".$fileactualext;  

        return $image_url;

    }
}


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $sql = "SELECT * FROM users ";

        $path = explode('/', $_SERVER['REQUEST_URI']);

        if(isset($path[4])) {
            $sql .= " WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $path[4]);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
            $response = $result;
            $response['imgProfile'] = getImageURL($result['profile']);
            $response['imgValidID'] = getImageID($result['valid_id']);

        } else {
            $sql .= " WHERE type !=  0";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = array();

            foreach ($list as $key => $value) {
                $response[] = [
                                'user_id' => $value['user_id'],
                                'username' => $value['username'],
                                'name' => $value['firstname'] . " " . $value['middlename'] . " " . $value['lastname'],
                                'type' => getUserType($value['type']),
                                'email' => $value['email'],
                                'contact_no' => $value['contact'],
                                'date_created' => $value['date_created']

                              ];
            }
        }

        echo json_encode($response);



        break;
    case "POST":
            
        $input = json_decode( file_get_contents('php://input'));
        $user_id = generateUserID();

        $username = $input->username;
        $password = $input->password;
        $type = $input->user_type;
        $password1 = md5($password);
        $datetime = date("Y-m-d");

        $check_users = "SELECT * FROM users WHERE username = :username";
        $stmt = $conn->prepare($check_users);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $existed = $stmt->fetch();
        

        if ($existed) {
            if ($existed['username'] === $username) {
                $response = ['status' => 0, 'message' => 'Username is already existed.'];
                echo json_encode($response);
                return false;
            }
        }else{
            $sql = "INSERT INTO users(user_id, username, password, type, firstname, lastname, middlename, date_created) VALUES(:user_id, :username, :password, :type, :firstname, :lastname, :middlename, :_datetime)";
                
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password1, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_INT);
            $stmt->bindParam(':firstname', $input->firstname, PDO::PARAM_STR);
            $stmt->bindParam(':lastname', $input->lastname, PDO::PARAM_STR);
            $stmt->bindParam(':middlename', $input->middlename, PDO::PARAM_STR);
            $stmt->bindParam(':_datetime',  $datetime);


            if($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Account created successfully.'];

            }else{
                $response = ['status' => 0, 'message' => 'Failed to create record.'];   
            }

            echo json_encode($response);
        }   


        break;
    case "PUT":

        $input = json_decode( file_get_contents('php://input'));
        $path = explode('/', $_SERVER['REQUEST_URI']);

        if(isset($path[4])) {
           
            if($path[4] === "verification") {
                $sql = "UPDATE users SET verification = :verification  WHERE user_id = :user_id";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $path[5]);
                $stmt->bindParam(':verification', $input->verification);

                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Verified Successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Verified Failed'];
                }
                echo json_encode($response);
            }   
   

        }


    

        break;
    case "DELETE":
        break;


}


?>