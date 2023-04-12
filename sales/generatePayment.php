<?php

function getPaymentPlan($year) {
    switch ($year) {
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

function getPaymentType($plan) {
    switch ($plan) {
        case 0:
            return 0.24;
        case 1:
            return 0.15;
        case 2:
            return 0;
        case 3:
            return 0;
        case 4:
            return 0;
        default:
            // code block to be executed if expression doesn't match any of the cases
            break;
    }
}

function generatePayment($product) {
    $lotprice = (int) $product['lotPrice'];
    $perpetaulCare = $lotprice * 0.15;
    $listprice = $perpetaulCare + $lotprice;
    $downPayment = $listprice * getPaymentType($product['plan']);

    if ($product['years'] === 0) {
        $monthlyAmortization = 0; 
    }else{
        $monthlyAmortization = ($listprice - ceil($downPayment)) / getPaymentPlan($product['years']);
    }

    

    $summation = [
        'lotPrice' => $lotprice,
        'perpetaulCare' => $perpetaulCare,
        'listPrice' => $listprice,
        'downPayment' => ceil($downPayment),
        'monthlyAmortization' => ceil($monthlyAmortization)
    ];

    return $summation;
}


?>