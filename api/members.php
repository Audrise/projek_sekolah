<?php
    session_start();
    include 'config.php';
    header('Content-Type: application/json');
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $result = $conn->query(
            "SELECT id, name, username FROM users WHERE role='siswa'"
        );

        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }

        echo json_encode($members);
    }

    if ($method === 'POST') {

        $data = json_decode(file_get_contents("php://input"), true);
        $pass = md5($data['password']);

        $stmt = $conn->prepare(
            "INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, 'siswa')"
        );
        $stmt->bind_param(
            "sss",
            $data['name'],
            $data['username'],
            $pass
        );

        echo json_encode([
            "status" => $stmt->execute() ? "success" : "error"
        ]);
    }

    if ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['password'])) {

            $stmt = $conn->prepare(
                "UPDATE users SET name=?, username=? WHERE id=?"
            );
            $stmt->bind_param(
                "ssi",
                $data['name'],
                $data['username'],
                $data['id']
            );

        } else {

            $pass = md5($data['password']);
            $stmt = $conn->prepare(
                "UPDATE users SET name=?, username=?, password=? WHERE id=?"
            );
            $stmt->bind_param(
                "sssi",
                $data['name'],
                $data['username'],
                $pass,
                $data['id']
            );
        }

        echo json_encode([
            "status" => $stmt->execute() ? "success" : "error"
        ]);
    }

    if ($method === 'DELETE') {

        $id = $_GET['id'];
        $conn->query("DELETE FROM users WHERE id=$id");

        echo json_encode([
            "status" => "success"
    ]);
}
