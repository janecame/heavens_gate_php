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




$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":
        $path = explode('/', $_SERVER['REQUEST_URI']);

        if (isset($path[3])) {
            $sql = "SELECT users.user_id, 
                users_details.firstname, 
                users_details.lastname,
                users_details.middlename,
                users_details.address,
                users_details.birthdate,
                users_details.age,
                users_details.sex,
                users_details.civil_status, 
                users_details.email,
                users_details.contact
                FROM users
                JOIN users_details
                ON users.user_id = users_details.user_id
                WHERE users.username = :username";
        
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $path[3], PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        }

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
                $sql = "INSERT INTO users(user_id, username, password, type) VALUES(:user_id, :username, :password, :type);
                        INSERT INTO users_details(user_id, firstname, lastname, email) VALUES(:user_id, :firstname, :lastname, :email)";
                    
                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password1, PDO::PARAM_STR);
                $stmt->bindParam(':type', $type);
                $stmt->bindParam(':firstname', $input->firstname, PDO::PARAM_STR);
                $stmt->bindParam(':lastname', $input->lastname, PDO::PARAM_STR);
                $stmt->bindParam(':email', $input->email, PDO::PARAM_STR);

                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Account created successfully.'];

                }else{
                    $response = ['status' => 0, 'message' => 'Failed to create record.'];   
                }

                echo json_encode($response);
            }   

        }
        break;

    case "PUT":

        $input = json_decode( file_get_contents('php://input'));
        
        $sql = "UPDATE users_details SET firstname= :firstname, middlename= :middlename, lastname=:lastname, sex=:sex, civil_status=:civil_status, birthdate=:birthdate, age=:age, contact=:contact, address=:address, email=:email WHERE user_id = :user_id";

        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':user_id', $input->user_id);        
        $stmt->bindParam(':firstname', $input->firstname);
        $stmt->bindParam(':middlename', $input->middlename);
        $stmt->bindParam(':lastname', $input->lastname);
        $stmt->bindParam(':sex', $input->sex);
        $stmt->bindParam(':civil_status', $input->civil_status);
        $stmt->bindParam(':birthdate', $input->birthdate);
        $stmt->bindParam(':age', $input->age);
        $stmt->bindParam(':contact', $input->contact);
        $stmt->bindParam(':address', $input->address);
        $stmt->bindParam(':email', $input->email);

        if($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Approved Successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Approved Failed'];
        }
        echo json_encode($response);
        break;

    case "DELETE":



        break;
}

?>