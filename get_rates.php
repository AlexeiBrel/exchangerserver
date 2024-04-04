<?php
header('Content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$conn = mysqli_connect("localhost", "root", "", "exchanger");

if (!$conn){
    $response = array("error" => "Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
    echo json_encode($response);
    exit();
}

// Проверяем наличие параметра date в запросе
if(isset($_GET['date'])) { //&& $_GET['cur']
    $date = $_GET['date'];
    // $cur = $_GET['cur'];
} else {
    $response = array("error" => "Ошибка: Не указан параметр date");
    echo json_encode($response);
    exit();
}

// Получаем данные о курсах валют для указанной даты
$currencyQuery = "SELECT * FROM currency_rates WHERE date = '$date'"; 
$currencyResult = mysqli_query($conn, $currencyQuery);

if (!$currencyResult) {
    $response = array("status" => "error", "message" => "Ошибка при выполнении запроса для получения курсов валют на дату: $date");
    echo json_encode($response);
    exit();
}

$mergedData = array();
$rates = array(); // Инициализируем массив для курсов валют
while ($currencyRow = mysqli_fetch_assoc($currencyResult)) {    
    // Добавляем курсы валют в массив
    $rates[$currencyRow["currency_code"]] = floatval($currencyRow["exchange_rate"]);
}

// Добавляем данные в общий массив
$mergedData[] = array(
    "date" => $date,
    "rates" => $rates
);

// Выводим объединенные данные в JSON
echo json_encode($mergedData);

mysqli_close($conn);
?>
