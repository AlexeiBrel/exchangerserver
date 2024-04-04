<?php
    header('Content-type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    session_start();

    $conn = mysqli_connect("localhost", "root", "", "exchanger");

    if (!$conn){
        $response = array("error" => "Ошибка: Невозможно подключиться к MySQL " . mysqli_connect_error());
        echo json_encode($response);
        exit();
    }    
    
    function loginUser($conn, $login, $role, $password) {       
        $sql = "SELECT * FROM users WHERE login='$login' and role='$role'";
        $result = $conn->query($sql);            

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            
            if (password_verify($password, $row['password'])) {         
                $_SESSION['id'] = $row['id'];    
                $_SESSION['username'] =  $row['username'];   
                $_SESSION['role'] = $row['role'];
                $_SESSION['login'] = $row['login'];
                              
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    // Обработка отправленной формы для аунтификации
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $data = json_decode(file_get_contents("php://input"), true);
        $login = $data['login'];        
        $role = $data['role'];            
        $password = $data['password'];            

        if (loginUser($conn, $login, $role, $password)) {       
            if (isset($_SESSION['login'])) {
                $sessionData = array("id" => $_SESSION['id'], "username" => $_SESSION['username'], "login" => $_SESSION['login'], 'role' => $_SESSION['role'] );     
                
                echo json_encode(array("status" => "ok", "session" => $sessionData, "message" => "Авторизация прошла успешна!"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Ошибка получения информации о сессии."));
            }
        } else {            
            echo json_encode(array("status" => "error", "message" => "Ошибка авторизации!"));
        }
    }        
?>