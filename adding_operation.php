<?php
header('Content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$conn = mysqli_connect("localhost", "root", "", "exchanger");

if (!$conn){
    $response = array("error" => "Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if(isset($data['user_id']) && isset($data['toCurrency']) && isset($data['toPrice']) && isset($data['rate']) && isset($data['fromCurrency']) && isset($data['fromPrice'])) {       
        $user_id = $data['user_id'];
        $date = date("Y-m-d");
        $toCurrency = $data['toCurrency'];
        $toPrice = $data['toPrice'];
        $rate = $data['rate'];
        $fromCurrency = $data['fromCurrency'];
        $fromPrice = $data['fromPrice'];
       

        // Проверяем, существует ли уже запись для указанной даты и валюты
        // $checkQuery = "SELECT * FROM sessions WHERE date = '$date' AND currency_code = '$currency_code' AND exchange_rate='$exchange_rate'";
        // $checkResult = $conn->query($checkQuery);

        // if ($checkResult->num_rows > 0) {
        //     $response = array("status" => "error", "message" => "Запись уже существует для указанной даты, валюты и курса обмена");
        //     echo json_encode($response);    
        // } else {
             // Записи нет, можно добавить новую
             $insertQuery = "INSERT INTO `exchangeoperations` (session_id, user_id, date, toCurrency, toPrice, rate, fromCurrency, fromPrice) 
             VALUES (NULL, '$user_id', '$date', '$toCurrency', '$toPrice', '$rate', '$fromCurrency', '$fromPrice')";

            // $insertResult = mysqli_query($conn, $insertQuery);

            if ($conn->query($insertQuery) === TRUE) {
                $response = array("status" => "ok", "message" => "Запись успешно добавлена");
                echo json_encode($response);
            } else {
                $response = array("status" => "error", "message" => "Ошибка при добавлении данных: " . mysqli_error($conn));
                echo json_encode($response);
            // }            
        }
    } else {
        $response = array("status" => "error", "message" => "Необходимые данные отсутствуют");
        echo json_encode($response);
    }
} else {
    $response = array("status" => "error", "message" => "Метод запроса должен быть POST");
    echo json_encode($response);
}

mysqli_close($conn);
?>
