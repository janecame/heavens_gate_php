

<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();


function plotType($val, $conn){
    
    $id = $val + 1;
    $sql = "SELECT plot_type FROM plot_type WHERE id = :id";

    $stmt = $conn->prepare($sql); 
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $response = $stmt->fetch(PDO::FETCH_ASSOC);
    return $response['plot_type'];
}


function userType($type, $conn){
    
    /*$sql = "SELECT type FROM users WHERE user_id = :id";

    $stmt = $conn->prepare($sql); 
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $response = $stmt->fetch(PDO::FETCH_ASSOC);*/

    if ($type === 2) {
        return "Broker";
    }else{
        return "Client";
    }
    
}

function getPlan($plan){
  switch($plan){
    case 0: 
      return "Full Down 24%";
    break;

    case 1: 
      return "Low Down 15%";
    break;

    case 2: 
      return "No Down";
    break;

    case 3: 
      return "Cash";
    break;

    case 4: 
      return "At Need";
    break;

    default: return "unknown";

    break;

  }
}

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":

        $date = "--";

    	$sql = "SELECT 
                    clients.client_id,
                    clients.client_type,  
                    clients.plot_id,
                    clients.firstname,
                    clients.lastname,
                    clients.middlename,
                    clients.reservation,  
                    accounts.lot_price, 
                    accounts.class, 
                    accounts.years, 
                    accounts.plan, 
                    accounts.type 
                    FROM clients
                    JOIN accounts
                    ON clients.plot_id = accounts.plot_id";



        $path = explode('/', $_SERVER['REQUEST_URI']);

        if(isset($path[4])) {
            $sql .= " WHERE plot_id = :plot_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':plot_id', $path[4]);
            $stmt->execute();
            $response = $stmt->fetch(PDO::FETCH_ASSOC);

        } else {
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = array();
            foreach ($orders as $key => $value) {
                $response[] = [
                                'plot_id' => $value['plot_id'],
                                'client_id' => $value['client_id'],
                                'name' => $value['firstname'] . " " . $value['middlename'] . " " . $value['lastname'],
                                'lot_price' => $value['lot_price'],
                                'years' => $value['years'], //." " . "years"
                                'class' => $value['class'],
                                'plan' => getPlan($value['plan']),
                                'reservation' => $value['reservation'],
                                'lot' => plotType($value['type'], $conn),
                                'user_type' => userType($value['client_type'], $conn),
                                'date_of_purchase' => $date,
                              ];
            }
            
            
        }

        echo json_encode($response);



        break;
    case "POST":

        
       
    
        break;

    case "PUT":
        $data = json_decode(file_get_contents('php://input'));
        $path = explode('/', $_SERVER['REQUEST_URI']);

        if(isset($path[4])) {
            if($path[4] === "approved"){
                    
                $sql = "UPDATE clients SET status = :status WHERE plot_id = :plot_id";
              
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':plot_id', $data->plot_id);
                $stmt->bindParam(':status', $data->status);

                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Approved Successfully'];
                } else {
                    $response = ['status' => 0, 'message' => 'Approved Failed.'];
                }
                echo json_encode($response);
            }
        
        return false;

        }
        break;

    case "DELETE":



        break;
}

?>