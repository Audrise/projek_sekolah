<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$action = isset($input['action']) ? $input['action'] : '';

if ($action === 'login') {

    $username = $input['username'];
    $password = md5($input['password']);
    $role     = $input['role'];

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE username = ? AND password = ? AND role = ?"
    );
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['name']     = $user['name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        echo json_encode([
            "status" => "success",
            "data"   => $user
        ]);
    } else {
        echo json_encode([
            "status"  => "error",
            "message" => "Username atau password salah"
        ]);
    }
}

if ($action === 'register') {

    $name     = $input['name'];
    $username = $input['username'];
    $password = md5($input['password']);

    $check = $conn->query(
        "SELECT id FROM users WHERE username = '$username'"
    );

    if ($check->num_rows > 0) {
        echo json_encode([
            "status"  => "error",
            "message" => "Username sudah ada"
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, 'siswa')"
    );
    $stmt->bind_param("sss", $name, $username, $password);

    if ($stmt->execute()) {
        echo json_encode([
            "status"  => "success",
            "message" => "Pendaftaran berhasil"
        ]);
    } else {
        echo json_encode([
            "status"  => "error",
            "message" => "Gagal mendaftar"
        ]);
    }
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode([
        "status" => "success"
    ]);
}
