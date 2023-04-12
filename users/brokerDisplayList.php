

<?php
include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

function getImageURL($profile, $imgType){
    
    $dir = 'profiles/';
    global $server;

    if ($profile == null && $imgType == "profile") {
        return $image_url = $server . $dir . "user.png";
    }else{

        $filename = "../profiles/" . $imgType . "-" . $profile .".*";
        $fileinfo = glob($filename);


        if (empty($fileinfo)) {
           return $image_url = $server . $dir . "user.png";
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


    	$sql = "SELECT * FROM users WHERE type = 2";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = array();
        foreach ($orders as $key => $value) {
            $response[] = [
                            'name' => $value['firstname'] . " " . $value['middlename'] . " " . $value['lastname'],
                            'profile_url' => getImageURL($value['profile'], "profile"),
                            'email' => $value['email'],
                            'contact' => $value['contact']
                          ];
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