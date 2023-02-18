
<?php
include 'headers.php';

include 'DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];
$dir = 'orders/';
$server = 'http://localhost/heavens_gate/';

function messageID($input, $conn){

    $message = "SELECT * FROM messages WHERE message_id = :message_id";
    $stmt = $conn->prepare($message);
    $stmt->bindParam(':message_id', $input->message_id, PDO::PARAM_STR);
    $stmt->execute();
    $existed = $stmt->fetch();
    
    $agent_id = "843785";

    if ($existed) {
        if ($existed['message_id'] === $input->message_id) {
            $response = ['status' => 0, 'message' => 'message_id is already existed.'];
            return $response;
        }
    }

    $sql = "INSERT INTO messages(message_id, agent_id, customer_id) VALUES(:message_id, :agent_id, :customer_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':message_id', $input->message_id);
    $stmt->bindParam(':customer_id', $input->customer_id);
    $stmt->bindParam(':agent_id', $agent_id);


    if($stmt->execute()) {
        $response = ['status' => 1, 'message' => 'Insert successfully.'];
    } else {
        $response = ['status' => 0, 'message' => 'Failed to post record.'];
    }
    return $response;      
}

switch($method) {
    case "GET":
        // $path = explode('/', $_SERVER['REQUEST_URI']);
        // $json_string = file_get_contents($server . $dir . 'messages_data/' . $path[3] . '_message.json');
        // $response = json_decode($json_string, true);
        // echo json_encode($response);

        $path = explode('/', $_SERVER['REQUEST_URI']);
        $filename = $path[3] . '_message.json';
        $file_path = 'orders/messages_data/' . $filename;


        if (file_exists($file_path)) {
            $json_string = file_get_contents($file_path);
            $response = json_decode($json_string, true);
            echo json_encode($response);
        } else {
            $response = array('status' => 0, 'message' => 'No Messages');
            echo json_encode($response);
        }

    break;
    case "POST":
        $input = json_decode( file_get_contents('php://input') );
        $message_id = $input->message_id;

        if (isset($input->message) && !empty($input->message)) {
            $message_data = array(
                'message' => $input->message,
                'user' => $input->user,
                'timestamp' => time()

            );
            
            $file = $dir . 'messages_data/' . $message_id . '_message.json';
            $current_data = file_get_contents($file);
            $array_data = json_decode($current_data, true);
            $array_data[] = $message_data;
            $final_data = json_encode($array_data, JSON_PRETTY_PRINT);
            
            if (!file_put_contents($file, $final_data, LOCK_EX)) {
                $response = ['status' => 0, 'message' => 'Failed to store.'];
                echo json_encode($response);
            }else{
                $response = messageID($input, $conn);
                echo json_encode($response);
            }

        }
        
        

        break;

    case "DELETE":

    break;
    case "PUT":

    break;

}