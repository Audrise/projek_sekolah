<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents("php://input"), true);

if ($method === 'GET') {

    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

    $sql = "
        SELECT 
            t.id,
            b.title AS book_title,
            u.name AS user_name,
            b.stock AS total_stock,
            t.borrow_date,
            t.return_date,
            t.status
        FROM transactions t
        JOIN books b ON t.book_id = b.id
        JOIN users u ON t.user_id = u.id
    ";

    if ($filter !== 'all') {
        $sql .= " WHERE t.status = '" . $conn->real_escape_string($filter) . "'";
    }

    $sql .= " ORDER BY t.id DESC";

    $result = $conn->query($sql);
    $trans = array();

    while ($row = $result->fetch_assoc()) {
        $trans[] = $row;
    }

    echo json_encode($trans);
    exit;
}

if ($method === 'POST') {

    $user_id = $input['user_id'];
    $book_id = $input['book_id'];

    $bookQuery = $conn->query(
        "SELECT stock FROM books WHERE id=" . intval($book_id)
    );

    if ($bookQuery->num_rows == 0) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Buku tidak ditemukan"
        ));
        exit;
    }

    $totalStock = $bookQuery->fetch_assoc()['stock'];

    $loanQuery = $conn->query(
        "SELECT COUNT(*) AS active_loans 
         FROM transactions 
         WHERE book_id=" . intval($book_id) . " AND status='dipinjam'"
    );

    $activeLoans = $loanQuery->fetch_assoc()['active_loans'];

    if ($activeLoans >= $totalStock) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Maaf, semua buku sedang dipinjam (stok habis)"
        ));
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO transactions 
        (user_id, book_id, borrow_date, status) 
        VALUES (?, ?, CURDATE(), 'dipinjam')"
    );
    $stmt->bind_param("ii", $user_id, $book_id);

    if ($stmt->execute()) {
        echo json_encode(array(
            "status" => "success",
            "message" => "Peminjaman berhasil"
        ));
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "Gagal memproses peminjaman"
        ));
    }
    exit;
}

if ($method === 'PUT') {

    $t_id = $input['id'];

    $tQuery = $conn->query(
        "SELECT * FROM transactions WHERE id=" . intval($t_id)
    );

    if (!$tQuery || $tQuery->num_rows == 0) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Transaksi tidak ditemukan"
        ));
        exit;
    }

    $t = $tQuery->fetch_assoc();

    if ($t['status'] === 'dikembalikan') {
        echo json_encode(array(
            "status" => "error",
            "message" => "Buku sudah dikembalikan"
        ));
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE transactions 
         SET status='dikembalikan', return_date=CURDATE() 
         WHERE id=?"
    );
    $stmt->bind_param("i", $t_id);

    if ($stmt->execute()) {
        echo json_encode(array(
            "status" => "success",
            "message" => "Buku berhasil dikembalikan"
        ));
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "Gagal mengembalikan buku"
        ));
    }
    exit;
}
