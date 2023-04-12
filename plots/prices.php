<?php

include '../headers.php';
include '../DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

function getPrice($prices, $plan){

  return $prices[$plan];

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

function getPaymentPlan($year){
    switch($year){
      case 1:
        return 12;
      case 2:
        return 24;
      case 3:
        return 36;
      default:
        // code block to be executed if expression doesn't match any of the cases
        break;
    }
}

function getPaymentType($plan){
  switch($plan){
      case 1:
        return 0.24;
      case 2:
        return 0.15;
      case 3:
        return 0;
      case 4:
        return 0;
      case 5:
        return 0;
      default:
        // code block to be executed if expression doesn't match any of the cases
        break;
  }
}


function generatePayment($price, $grp, $years) {
    $lotprice = (int)$price;
    $perpetaulCare = $lotprice * 0.15;
    $listprice = $perpetaulCare + $lotprice;
    $downPayment = $listprice * getPaymentType($grp); 
    $monthlyAmortization = ($listprice - ceil($downPayment)) / getPaymentPlan($years); 

    $summation = [
        'perpetaulCare' => $perpetaulCare,
        'listPrice' => $listprice,
        'downPayment' => ceil($downPayment),
        'monthlyAmortization' => ceil($monthlyAmortization),
    ];

    return $summation;
}






function getPrices($class, $conn){


      $response = array();

      $sql = "SELECT years, fulldown, lowdown, nodown, cash, at_need FROM lot_prices WHERE class = :class ORDER BY years";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':class', $class);
      $stmt->execute();
      $result = $stmt->fetchall(PDO::FETCH_ASSOC);

      
      $data = array();
      foreach ($result as $row) {
          $data[] = array_values($row);
      }

      $grpMap = [
          1 => 'Full down 24%',
          2 => 'Lowd own 15%',
          3 => 'No down'
      ];

      foreach ($grpMap as $grp => $grpName) {
          $planData = array(
              'plan' => $grpName,
              'prices' => array()
          );

          for ($i = 0; $i < count($data); $i++) {
              $year = $data[$i][0];
              $cash = $data[$i][4];
              $at_need = $data[$i][5];
              $price = $data[$i][array_search($grpName, $grpMap)];

              $planData['prices'][] = array(
                  'grp' => $grp,
                  'years' => $year,
                  'lotPrice' => intval($price),
                  ...generatePayment($price, $grp, $year),
                  'cash' => $cash,
                  'at_need' => $at_need
              );
          }

          $response[] = $planData;
      }

      return $response;

}


function getSituation1($data, $conn){

    $response = array();
    
    $sql = "SELECT * FROM lot_prices WHERE type = :type && class = :class && years = :years";
      
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':type', $data->type);
    $stmt->bindParam(':class', $data->class);
    $stmt->bindParam(':years', $data->years);
    
    $stmt->execute();

    $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($product as $key => $value) {
        $response[] = [
                      'id' => $value['id'],
                      'type' => $value['type'],
                      'class' => $value['class'],
                      'years' => $value['years'],
                      'lotPrice' => getPrice([$value['fulldown'], $value['lowdown'], $value['nodown'], $value['cash'], $value['at_need']], $data->plan),
                      'plan' => $data->plan,
                      'planDesc' => getPlan($data->plan),
                      'prices' => [$value['fulldown'], $value['lowdown'], $value['nodown']]
                    ];
    
    }

    return $response;

}

function getSituation2($data, $conn){

    $response = array();
    
    $sql = "SELECT id, type, class, fulldown, lowdown, nodown, cash, at_need FROM `lot_prices` WHERE type=:type && class = :class GROUP BY cash";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':type', $data->type);
    $stmt->bindParam(':class', $data->class);
    
    $stmt->execute();

    $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($product as $key => $value) {
        $response[] = [
                      'id' => $value['id'],
                      'type' => $value['type'],
                      'class' => $value['class'],
                      'years' => 0,
                      'lotPrice' => getPrice([$value['fulldown'], $value['lowdown'], $value['nodown'], $value['cash'], $value['at_need']], $data->plan),
                      'plan' => $data->plan,
                      'planDesc' => getPlan($data->plan)
                    ];
    
    }

    return $response;

}

function getSituation3($data, $conn){

    $response = array();
    
    $sql = "SELECT * FROM `lot_prices` WHERE type = :type && years = :years";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':type', $data->type);
    $stmt->bindParam(':years', $data->years);
    
    $stmt->execute();

    $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($product as $key => $value) {
        $response[] = [
                      'id' => $value['id'],
                      'type' => $value['type'],
                      'class' => "no class",
                      'years' => $value['years'],
                      'lotPrice' => getPrice([$value['fulldown'], $value['lowdown'], $value['nodown'], $value['cash'], $value['at_need']], $data->plan),
                      'plan' => $data->plan,
                      'planDesc' => getPlan($data->plan)
                    ];
    
    }

    return $response;

}

function getSituation4($data, $conn){

    $response = array();
    
    $sql = "SELECT id, type, fulldown, lowdown, nodown, cash, at_need FROM `lot_prices` WHERE type=:type GROUP BY cash";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':type', $data->type);
    
    $stmt->execute();

    $product = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($product as $key => $value) {
        $response[] = [
                      'id' => $value['id'],
                      'type' => $value['type'],
                      'class' => "no class",
                      'years' => 0,
                      'lotPrice' => getPrice([$value['fulldown'], $value['lowdown'], $value['nodown'], $value['cash'], $value['at_need']], $data->plan),
                      'plan' => $data->plan,
                      'planDesc' => getPlan($data->plan)
                    ];
    
    }

    return $response;

}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case "GET":

    $path = explode('/', $_SERVER['REQUEST_URI']);
    $response = array();


      if ($path[4] == "plotPrice") {
      
        $sql = "SELECT * FROM lot_prices WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $path[5]);
        
        $stmt->execute();

        $product = $stmt->fetchall(PDO::FETCH_ASSOC);


        if (intval($path[6]) === 3 || intval($path[6]) === 4) {
          foreach ($product as $key => $value) {
            $response[] = [
                          'id' => $value['id'],
                          'type' => $value['type'],
                          'class' => $value['class'],
                          'years' => 0,
                          'lotPrice' => getPrice([$value['fulldown'], $value['lowdown'], $value['nodown'], $value['cash'], $value['at_need']], intval($path[6])),
                          'plan' =>  intval($path[6])
                        ]; 
          }
          
        }else{
          foreach ($product as $key => $value) {
            $response[] = [
                          'id' => $value['id'],
                          'type' => $value['type'],
                          'class' => $value['class'],
                          'years' => $value['years'],
                          'lotPrice' => getPrice([$value['fulldown'], $value['lowdown'], $value['nodown'], $value['cash'], $value['at_need']], intval($path[6])),
                          'plan' =>  intval($path[6])
                        ]; 
          }
        }

        
        echo json_encode($response);           
        return false;
      }

      if ($path[4] === "prices") {

        $response = getPrices($path[5], $conn);

        echo json_encode($response);           
        return false;
      }

    break;  

    case "POST":

    $data = json_decode(file_get_contents('php://input'));
    $path = explode('/', $_SERVER['REQUEST_URI']);
    $response = array();


    if(isset($path[4])){


      if($data->years === 0 && $data->class === ""){
        $result = getSituation4($data, $conn);
        echo json_encode($result);

      }else if($data->class === ""){
        $result = getSituation3($data, $conn);
        echo json_encode($result);
        
      }else if($data->years === 0){
        $result = getSituation2($data, $conn);
        echo json_encode($result);
      }else{
        $result = getSituation1($data, $conn);
        echo json_encode($result);
      }
    
    }else{

      $sql = "INSERT INTO lot_prices (type, class, years, fulldown, lowdown, nodown) VALUES (:type, :class, :years, :fulldown, :lowdown, :nodown)";
  
        foreach ($data as $classData) {
          $class = $classData->class;

          foreach ($classData->payments as $payment) {
            $year = $payment->year;
            $prices = $payment->prices;

            $stmt = $conn->prepare($sql);
            $stmt->execute([
              ':type' => 'year_price',
              ':class' => $class,
              ':years' => $year,
              ':fulldown' => $prices[0],
              ':lowdown' => $prices[1],
              ':nodown' => $prices[2]
            ]);
          }
        }

        echo json_encode("Success ");
    }

  

    break;
    case "DELETE":

    break;
    case "PUT":

    break;

}
?>