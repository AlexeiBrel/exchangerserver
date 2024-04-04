<?php
header('Content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$conn = mysqli_connect("localhost", "root", "", "exchanger");

if (!$conn){
    $response = array("error" => "Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
    echo json_encode($response);
    exit();
}

// Получаем уникальные даты из таблицы
$datesQuery = "SELECT DISTINCT date FROM currency_rates";
$datesResult = mysqli_query($conn, $datesQuery);

if (!$datesResult) {
    $response = array("status" => "error", "message" => "Ошибка при выполнении запроса для получения дат");
    echo json_encode($response);
    exit();
}

$dates = array();
// Получаем даты и добавляем их в массив
while ($row = mysqli_fetch_assoc($datesResult)) {
    $dates[] = $row["date"];
}

// Получаем данные о курсах валют для каждой даты
$data = array();
foreach ($dates as $date) {
    $currencyQuery = "SELECT * FROM currency_rates WHERE date = '$date'";
    $currencyResult = mysqli_query($conn, $currencyQuery);

    if ($currencyResult) {
        $rates = array();
        while ($row = mysqli_fetch_assoc($currencyResult)) {
            $rates[] = array(
                "id" => $row["id"],
                "currency_code" => $row["currency_code"],
                "total" => $row["total"],
                "currency_name" => $row["currency_name"],
                $row["currency_code"] => $row["exchange_rate"]
            );
        }
        $data[$date] = $rates;
    } else {
        $response = array("status" => "error", "message" => "Ошибка при выполнении запроса для получения курсов валют на дату: $date");
        echo json_encode($response);
        exit();
    }
}

// Выводим объединенные данные в JSON
$mergedData = array("dates" => $dates, "currency_data" => $data);
echo json_encode($mergedData);

mysqli_close($conn);
?>
