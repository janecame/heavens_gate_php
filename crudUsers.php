<?php
include 'headers.php';
include 'DbConnect.php';
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

function generateFileName() {
    $date = date("y");
    $keyLength = 4;
    $str = "abcdefghijklmnopqrstuvwxyz";
    $randStr = substr(str_shuffle($str), 0, $keyLength) . '-' .
               substr(str_shuffle($str), 0, $keyLength);
    
    return $randStr;
}

function getImageURL($profile, $imgType){
    
    $dir = 'profiles/';
    global $server;

    if ($profile == null && $imgType == "profile") {
        return $image_url = $server . $dir . "user.png";
    }else{

        $filename = "profiles/" . $imgType . "-" . $profile .".*";
        $fileinfo = glob($filename);


        if (empty($fileinfo)) {
           return 0;
        }

        $filepath = $fileinfo[0];
        $fileext = explode(".", $filepath);
        $fileactualext = $fileext[count($fileext) - 1];
        $image_url = $server . $dir . $imgType . "-" . $profile . "." . $fileactualext;  

        return $image_url;

    }

   

}


$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        

        $path = explode('/', $_SERVER['REQUEST_URI']);
        //if (empty($path)) {
        $sql = "SELECT *  FROM users WHERE username = :username";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $path[3], PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response = $result;
        $response['imgProfile'] = getImageURL($result['profile'], "profile");
        $response['imgESign'] = getImageURL($result['eSign'], "esign");
        echo json_encode($response);
        //}

        break;
    case "POST":
        

        $path = explode('/', $_SERVER['REQUEST_URI']);

        if ($path[3] === 'login') {
            $input = json_decode( file_get_contents('php://input'));
            $username = $input->username;
            $password = $input->password;
            $password1 = md5($password);
         
            $query = "SELECT COUNT(*) as count, type FROM `users` WHERE `username` = :username AND `password` = :password";
            $stmt = $conn->prepare($query);        

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password1);

            $stmt->execute();
            $row = $stmt->fetch();
         
            $count = $row['count'];
            $usertype = $row['type'];

            if($count > 0){
                $response = ['status' => 1, 'message' => 'Successfully Login.',  'username' => $username, 'usertype' => $usertype];
            }else{
                $response = ['status' => 0, 'message' => 'Wrong both username and password.'];
            }
            echo json_encode($response);
            //return false
        }
        if ($path[3] === 'reg') {

            $input = json_decode( file_get_contents('php://input'));
            $user_id = generateUserID();
            $username = $input->username;
            $password = $input->password1;
            $type = 0;
            $password1 = md5($password);

    
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
                $sql = "INSERT INTO users(user_id, username, password, type, firstname, lastname, email, contact) VALUES(:user_id, :username, :password, :type, :firstname, :lastname, :email, :contact)";
                    
                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password1, PDO::PARAM_STR);
                $stmt->bindParam(':type', $type);
                $stmt->bindParam(':firstname', $input->firstname, PDO::PARAM_STR);
                $stmt->bindParam(':lastname', $input->lastname, PDO::PARAM_STR);
                $stmt->bindParam(':email', $input->email, PDO::PARAM_STR);
                $stmt->bindParam(':contact', $input->contact, PDO::PARAM_STR);

                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Account created successfully.'];

                }else{
                    $response = ['status' => 0, 'message' => 'Failed to create record.'];   
                }

                echo json_encode($response);
            }   

        }


        if ($path[3] === "upload-profile") {

            $user_id = $path[4];
            $filename = generateFileName();
            $fileNameNew = "profile-".$filename."."."jpg";

            move_uploaded_file($_FILES["file"]["tmp_name"], "profiles/" . $fileNameNew);

            $sql = "UPDATE users SET profile = :filename WHERE user_id = :user_id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':filename', $filename);

            if($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Upload Successfully.'];
            } else {
                $response = ['status' => 0, 'message' => 'Upload Failed '];
            }

            echo json_encode($response);
        }

        if ($path[3] === "upload-esign") {

            $user_id = $path[4];
            $filename = generateFileName();
            $fileNameNew = "esign-".$filename."."."jpg";

            move_uploaded_file($_FILES["file"]["tmp_name"], "profiles/" . $fileNameNew);

            $sql = "UPDATE users SET eSign = :filename WHERE user_id = :user_id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':filename', $filename);

            if($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Upload ESign Successfully.'];
            } else {
                $response = ['status' => 0, 'message' => 'Upload Failed '];
            }

            echo json_encode($response);
        }

        if ($path[3] === "upload-id") {

            $user_id = $path[4];
            $filename = generateFileName();
            $fileNameNew = "id-".$filename."."."jpg";

            move_uploaded_file($_FILES["file"]["tmp_name"], "verifications/" . $fileNameNew);

            $sql = "UPDATE users SET verification = 2, valid_id = :filename, date_change = NOW() WHERE user_id = :user_id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':filename', $filename);

            if($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Upload Successfully.'];
            } else {
                $response = ['status' => 0, 'message' => 'Upload Failed '];
            }

            echo json_encode($response);
        }

        break;

    case "PUT":

        break;
        
    case "DELETE":



        break;
}

?>