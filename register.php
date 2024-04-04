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
    
    function registerUser($conn, $username, $login, $role, $password) {
        // Проверяем, существует ли уже пользователь с таким логином и ролью
        $sqlCheck = "SELECT * FROM users WHERE login='$login' AND role='$role'";
        $resultCheck = $conn->query($sqlCheck);
    
        if ($resultCheck->num_rows > 0) {
            // Если пользователь уже существует, вернем ошибку
            return false;
        } else {
            // Если пользователя нет, создаем новую запись
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sqlInsert = "INSERT INTO users (`username`, `login`, `role`, `password`) VALUES ('$username', '$login', '$role', '$hashedPassword')";
    
            if ($conn->query($sqlInsert) === TRUE) {
                // Регистрация прошла успешно
                return true;
            } else {
                // Если произошла ошибка при выполнении запроса к БД
                return false;
            }
        }
    }
    
    
    // Обработка отправленной формы для регистрации
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $data = json_decode(file_get_contents("php://input"), true);
        $username = $data['username'];
        $login = $data['login'];
        $role = $data['role'];
        $password = $data['password'];
     
        if (registerUser($conn, $username, $login, $role, $password)) {     
            echo json_encode(array("status" => "ok", "message" => "Регистрация прошла успешно!"));
        } else {            
            echo json_encode(array("status" => "error", "message" => "Ошибка регистрации."));
        }
    }
    
?>
