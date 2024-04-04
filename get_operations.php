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
if(isset($_GET['date']) && $_GET['date'] !== '') { 
    $date = $_GET['date'];    
    $date_condition = "";
} else {
    $response = array("error" => "Ошибка: Не указан параметр date");
    echo json_encode($response);
    exit();
}

// Проверяем, передан ли параметр user_id
if(isset($_GET['user_id']) && $_GET['user_id'] !== '') { 
    $user_id = $_GET['user_id'];
    $user_condition = "AND user_id = '$user_id'";
} else {
    $user_condition = "";
}

// Проверяем, передан ли параметр user_id
if(isset($_GET['cur']) && $_GET['cur'] !== '') { 
    $cur = $_GET['cur'];
    $cur_condition = "AND fromCurrency = '$cur'";
} else {
    $cur_condition = "";
}


// Получаем данные о курсах валют для указанной даты и идентификатора пользователя
$currencyQuery = "SELECT * FROM exchangeoperations FULL JOIN users ON user_id = users.id WHERE date = '$date' $user_condition $cur_condition order by session_id desc";
// echo $currencyQuery;
$currencyResult = mysqli_query($conn, $currencyQuery);

if (!$currencyResult) {
    $response = array("status" => "error", "message" => "Ошибка при выполнении запроса для получения курсов валют на дату: $date");
    echo json_encode($response);
    exit();
}

// Создаем массив для хранения данных о курсах валют
$currencyData = array();

// Извлекаем данные из результата запроса и добавляем их в массив
while ($row = mysqli_fetch_assoc($currencyResult)) {
    $currencyData[] = $row;
}

// Получаем список всех уникальных дат
$dateQuery = "SELECT DISTINCT date FROM exchangeoperations";
$dateResult = mysqli_query($conn, $dateQuery);

if (!$dateResult) {
    $response = array("status" => "error", "message" => "Ошибка при выполнении запроса для получения списка дат");
    echo json_encode($response);
    exit();
}

// Создаем массив для хранения списка дат
$dateData = array();

// Извлекаем даты из результата запроса и добавляем их в массив
while ($dateRow = mysqli_fetch_assoc($dateResult)) {
    $dateData[] = $dateRow['date'];
}

// Получаем список всех уникальных user_id
$idQuery = "SELECT DISTINCT id, username, role FROM users";
$idResult = mysqli_query($conn, $idQuery);

if (!$idResult) {
    $response = array("status" => "error", "message" => "Ошибка при выполнении запроса для получения списка id");
    echo json_encode($response);
    exit();
}

// Создаем массив для хранения списка user_id
$idData = array();

// Извлекаем user_id из результата запроса и добавляем их в массив
while ($idRow = mysqli_fetch_assoc($idResult)) {
    if($idRow['role'] === 'operator') {
        $idData[] = $idRow;
    }
}


// Получаем список всех уникальных user_id
$curQuery = "SELECT DISTINCT toCurrency FROM exchangeoperations";
$curResult = mysqli_query($conn, $curQuery);

if (!$curResult) {
    $response = array("status" => "error", "message" => "Ошибка при выполнении запроса для получения списка валют");
    echo json_encode($response);
    exit();
}

// Создаем массив для хранения списка id
$curData = array();

// Извлекаем id из результата запроса и добавляем их в массив
while ($curRow = mysqli_fetch_assoc($curResult)) {    
    $curData[] = $curRow['toCurrency'];    
}

// Формируем итоговый JSON ответ
$response = array(
    "currency_data" => $currencyData,
    "dates" => $dateData,
    "user_ids" => $idData,
    "toCurrency" => $curData
);

// Выводим данные в формате JSON
echo json_encode($response);

mysqli_close($conn);
?>
