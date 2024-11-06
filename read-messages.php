<?php
    require_once __DIR__ . '/database.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $database = new Database();
            $conditions = [
                "sender_id" => $_POST['sender_id'],
                "receiver_id" => $_POST['receiver_id']
            ];
           
            $database->update("messages", ["is_read" => 1], $conditions);
            http_response_code(200);
            echo json_encode(['status' => true]);
            exit();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }

    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
?>