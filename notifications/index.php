<?php
include '../headers.php';

include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

/*function getNotificationType($val){
    switch ($val) {
      case 0:
        return "Your transaction decline";
        break;
      case 1:
        return "Your transaction Approved";
        break;
      default:
        return "unknown";
    }
}*/


function getNotificationType($val, $conn){

    $sql = "SELECT phrase
        FROM phrase_notification
        WHERE type = :type";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':type', $val);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $res['phrase'];
}

function getPosition($val){
    switch ($val) {
      case 0:
        return "Client";
        break;
      case 1:
        return "Admin";
        break;
      case 2:
        return "Broker";
        break;
      case 3:
        return "Sales";
        break;
      default:
        return "unknown";
    }
}

function getNotifier($id, $conn){
    
    $sql = "SELECT firstname, lastname FROM users WHERE user_id = :id";

    $stmt = $conn->prepare($sql); 
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $response = $stmt->fetch(PDO::FETCH_ASSOC);

    $firstname = $response['firstname'];
    $lastname = $response['lastname'];
    $name = $lastname . " " . substr($firstname, 0, 1) . ".";

    return $name;
    
    
}

function getNotifierPosition($id, $conn){
    
    $sql = "SELECT type FROM users WHERE user_id = :id";

    $stmt = $conn->prepare($sql); 
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $response = $stmt->fetch(PDO::FETCH_ASSOC);

    $result = $response['type'];
    $position = getPosition($result);

    return $position;
    
    
}

function getProfileFileName($id, $conn){

    $sql = "SELECT profile FROM users WHERE user_id = :id";
    $stmt = $conn->prepare($sql); 
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $response = $stmt->fetch(PDO::FETCH_ASSOC);

    $filename = $response['profile'];
    return $filename;
}
function getImageURL($id, $conn){
    
    $dir = 'profiles/';
    
    //$server = 'http://localhost/heavens_gate/';
    global $server;
    
    $profile = getProfileFileName($id, $conn);

    if ($profile == null) {
        return $image_url = $server . $dir . "user.png";
    }else{

        $filename = "../profiles/profile-".$profile.".*";
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

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $path = explode('/', $_SERVER['REQUEST_URI']);

        if ($path[5] === "list") {
            $sql = "SELECT notification, notifier, receiver , date_time, notificationType
                FROM notifications
                INNER JOIN users ON notifications.receiver = users.user_id
                WHERE users.username = :username";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $path[4]);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = array();

            foreach ($res as $key => $value) {
                $response[] = [
                                'from' => getNotifier($value['notifier'], $conn),
                                'whatIs' => getNotificationType($value['notificationType'], $conn),
                                'profile' => getImageURL($value['notifier'], $conn),
                                'user_id' => $value['receiver'],
                                'datetime' => $value['date_time']
                              ];
            }
            echo json_encode($response);
            return false;
        }
        


        if ($path[5] === "mainlist") {

            $sql = "SELECT * FROM notifications WHERE receiver = :receiver";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':receiver', $path[4]);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($res as $key => $value) {
                $response[] = [
                                'profile' => getImageURL($value['notifier'], $conn),
                                'name' => getNotifier($value['notifier'], $conn),
                                'position' => getNotifierPosition($value['notifier'], $conn),
                                'description' => $value['notification'],
                                'date' => $value['date_time']
                              ];
            }
            echo json_encode($response);

            return false;
        }
        
        
        

    break;
    case "POST":
        $input = json_decode( file_get_contents('php://input') );
    

        $sql = "INSERT INTO notifications(notifier, plot_id, notification, type)VALUES(:notifier, :plot_id, :notification, :type)";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':notifier', $input->notifier);
        $stmt->bindParam(':plot_id', $input->plot_id);
        $stmt->bindParam(':notification', $input->notification);
        $stmt->bindParam(':type', $input->type, PDO::PARAM_INT);


        if($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Issue sent successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Issue sent failed'];
        }


        echo json_encode($response);
        break;

    case "DELETE":

    break;
    case "PUT":

    break;

}