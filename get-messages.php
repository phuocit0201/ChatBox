<?php
    require_once __DIR__ . '/database.php';

    $database = new Database();
    $sql = "
        SELECT * FROM (
            SELECT *
            FROM messages
            WHERE (sender_id = " . $_GET['sender_id'] . " AND receiver_id = ". $_GET['receiver_id'] . ")
            OR (sender_id = ". $_GET['receiver_id'] . " AND receiver_id = " . $_GET['sender_id'] . ")
            ORDER BY created_at DESC
            LIMIT ". $_GET['limit'] . "
        ) AS recent_messages
        ORDER BY created_at;
    ";
    $messages = $database->executeQuery($sql);
    http_response_code(200);
    echo json_encode(['status' => true, 'messages' => $messages]);
    exit();
?>