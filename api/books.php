<?php
    session_start();
    include 'config.php';

    header('Content-Type: application/json');

    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
        echo json_encode([
            "status" => "error",
            "message" => "Unauthorized"
        ]);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {

        $result = $conn->query("SELECT * FROM books");
        $books = [];

        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        echo json_encode($books);
    }

    if ($method === 'POST') {

        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $conn->prepare(
            "INSERT INTO books (title, author, stock) VALUES (?, ?, ?)"
        );
        $stmt->bind_param(
            "ssi",
            $data['title'],
            $data['author'],
            $data['stock']
        );

        echo json_encode([
            "status" => $stmt->execute() ? "success" : "error"
        ]);
    }

    if ($method === 'PUT') {

        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $conn->prepare(
            "UPDATE books SET title=?, author=?, stock=? WHERE id=?"
        );
        $stmt->bind_param(
            "ssii",
            $data['title'],
            $data['author'],
            $data['stock'],
            $data['id']
        );

        echo json_encode([
            "status" => $stmt->execute() ? "success" : "error"
        ]);
    }

    if ($method === 'DELETE') {

        $id = $_GET['id'];
        $conn->query("DELETE FROM books WHERE id=$id");

        echo json_encode([
            "status" => "success"
    ]);
}
