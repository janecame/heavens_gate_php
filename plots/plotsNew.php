
<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];

//$json_string = file_get_contents('plots.json');
//$plots = json_decode($json_string, true);


function refId() {
    $date = date("y");
    $keyLength = 4;
    $str = "12345678ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randStr = substr(str_shuffle($str), 0, $keyLength) . '-' .
               substr(str_shuffle($str), 0, $keyLength);
    
    return $randStr;
}


/*function updateJson($plots, $data){
    //$response = "";
    $status = intval($data->status);

    $plots[0]['group'][$data->plot_id]['status'] = $status;


    $jsonData = json_encode($plots, JSON_PRETTY_PRINT);
    if (file_put_contents('plots.json', $jsonData)) {
        $response = ['message' => 'JSON data was successfully written', 'status' => 1];
    } else {
        $response = ['message' => 'Unable to write JSON data', 'status' => 0];

    }
    return $response;
}*/

switch($method) {
    case "GET":

        $sql = "SELECT * FROM plots";

        $path = explode('/', $_SERVER['REQUEST_URI']);
        
        $response = array();


        if(isset($path[4])) {

            $sql .= " WHERE status = :status";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':status', $path[4]);
            $stmt->execute();
            $plots = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($plots as $key => $value) {
                $response[] = [
                                'lat' => doubleval($value['lat']),
                                'lng' => doubleval($value['lng']),
                                'status' => intval($value['status']),
                                'plot_type' => intval($value['plot_type'])
                            ];
            
            }

        } else {

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $plots = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($plots as $key => $value) {
                $response[] = [
                                'lat' => doubleval($value['lat']),
                                'lng' => doubleval($value['lng']),
                                'status' => intval($value['status']),
                            ];
            
            }
        }

        echo json_encode($response);
    break;

    case "POST":

        $data = json_decode(file_get_contents('php://input'));
        $ref_id = refId();

        $sql = "INSERT INTO plots(id, lat, lng, plot_id, status) VALUES(:id, :lat, :lng, :plot_id, :status)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $data->id);
        $stmt->bindParam(':lat', $data->lat);
        $stmt->bindParam(':lng', $data->lng);
        $stmt->bindParam(':plot_id', $plot_id);
        $stmt->bindParam(':status', $data->status);


        if($stmt->execute()) {
            //$response = updateJson($plots, $data);
            $response = ['status' => 1, 'message' => 'Success to update.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to post record.'];
        }
        echo json_encode($response);

        break;

    case "DELETE":

    break;
    case "PUT":

        $data = json_decode(file_get_contents('php://input'));
        $plot_id = refId();

        $sql = "UPDATE plots SET status = :status, plot_id = :plot_id, plot_type = :type WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $data->id);
        $stmt->bindParam(':status', $data->status);
        $stmt->bindParam(':type', $data->type);
        $stmt->bindParam(':plot_id', $plot_id);


        if($stmt->execute()) {
            //$response = updateJson($plots, $data);
            $response = ['status' => 1, 'message' => 'Success to update.'];
            
        } else {
            $response = ['status' => 0, 'message' => 'Failed to update.'];
        }
        echo json_encode($response);

    break;

}