<?php
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/database.php';

    $pusher = new Pusher\Pusher(
        'fbf5f6ad5ae421e5adb8',
        '4839522da764b0026a18',
        '1889854',
        array(
            'cluster' => 'ap1',
            'useTLS' => true
        )
    );

    $database = new Database();
    $data = [
      'sender_id' => $_POST['sender_id'],
      'receiver_id' => $_POST['receiver_id'],
      'content' => $_POST['content']
    ];

    if ($database->create('messages', $data)) {
      $pusher->trigger('chat-channel', "message-receiver-" . $data['receiver_id'], $data);
      http_response_code(200);
      echo json_encode(['status' => true, 'message' => 'Tin nhắn đã được gửi']);
      exit();
    } else {
      http_response_code(500);
      echo json_encode(['status' => false, 'message' => 'Lỗi không gửi được tin nhắn']);
      exit();
    }
?>