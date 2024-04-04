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
    
    if(isset($data['total']) && isset($data['currency_code']) && isset($data['currency_name']) && isset($data['exchange_rate'])) {
        $total = $data['total'];
        $currency_code = $data['currency_code'];
        $currency_name = $data['currency_name'];
        $exchange_rate = $data['exchange_rate'];
        $date = date("Y-m-d");

        // Проверяем, существует ли уже запись для указанной даты и валюты
        $checkQuery = "SELECT * FROM currency_rates WHERE date = '$date' AND currency_code = '$currency_code' AND exchange_rate='$exchange_rate'";
        $checkResult = $conn->query($checkQuery);

        if ($checkResult->num_rows > 0) {
            $response = array("status" => "error", "message" => "Запись уже существует для указанной даты, валюты и курса обмена");
            echo json_encode($response);    
        } else {
             // Записи нет, можно добавить новую
             $insertQuery = "INSERT INTO currency_rates (date, total, currency_code, currency_name, exchange_rate) 
             VALUES ('$date', '$total', '$currency_code', '$currency_name', '$exchange_rate')";

            // $insertResult = mysqli_query($conn, $insertQuery);

            if ($conn->query($insertQuery) === TRUE) {
                $response = array("status" => "ok", "message" => "Запись успешно добавлена");
                echo json_encode($response);
            } else {
                $response = array("status" => "error", "message" => "Ошибка при добавлении данных");
                echo json_encode($response);
            }            
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
