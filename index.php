<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Box</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        #chatbox {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 400px;
            height: 500px;
            border: 1px solid #ccc;
            background-color: #fff;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            display: none;
            /* Initially hidden */
        }

        #chatbox-header {
            /* background-color: #007bff; */
            background-color: #fff;
            color: #fff;
            padding: 20px;
            /* Increase padding to make header taller */
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top-left-radius: 10px;
            /* Add border-radius to bottom left */
            border-top-right-radius: 10px;
            /* Add border-radius to bottom right */
            border-bottom: 1px solid #ccc;
            color: #050505;
            font-size: 22px;
            font-weight: 550;
        }

        #chatbox-header i {
            font-size: 24px;
            /* Increase icon size */
        }

        #chatbox-messages {
            flex: 1;
            padding: 10px;
            overflow-y: auto;
            height: 380px;
            overflow-y: scroll;
        }

        #chatbox-input {
            display: flex;
            border-top: 1px solid #ccc;
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        #chatbox-input input {
            flex: 1;
            padding: 10px;
            border: none;
            border-right: 1px solid #ccc;
            /* border-radius: 5px 0 0 5px; */
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            font-size: 14px;
        }

        #chatbox-input button {
            padding: 10px;
            border: none;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            /* border-radius: 0 5px 5px 0; */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            font-size: 14px;
        }

        #chatbox-input input:focus {
            outline: none;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        #chatbox-input button:hover {
            background-color: #0056b3;
        }

        #toggle-chatbox {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            font-size: 24px;
            /* Increase the font size */
        }

        #close-chatbox {
            cursor: pointer;
        }

        #close-chatbox:hover {
            color: #ff0000;
            /* Change color on hover */
        }

        .message {
            padding: 10px;
            margin: 5px;
            border-radius: 5px;
            max-width: 70%;
            word-wrap: break-word;
        }

        .message.self {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            align-self: flex-end;
            margin-left: auto;
            /* Align to the right */

        }

        .message.other {
            display: inline-block;
            background-color: #e0e0e0;
            color: #000;
            align-self: flex-start;
        }

        .message.sending {
            background-color: #f0f0f0;
            color: #999;
        }

        .message.error {
            background-color: #ffdddd;
            color: #d8000c;
            border: 1px solid #d8000c;
            border-radius: 5px;
            padding: 10px;
            margin: 5px;
            max-width: 70%;
            word-wrap: break-word;
            align-self: flex-end;
            margin-left: auto;
        }

        .message-date--content {
            text-align: center;
            font-weight: bold;
            color: #888;
            font-size: 14px;
            background-color: #f1f1f1;
            padding: 5px 10px;
            border-radius: 10px;
        }

        .message-date {
            text-align: center;
            font-weight: bold;
            color: #888;
            padding: 15px 0px;

        }

        #message-icon {
            position: relative;
            display: inline-block;
        }

        #message-icon .fa-envelope {
            font-size: 24px;
        }

        #unread-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
            display: none;
        }
    </style>
</head>

<body>
    <button id="toggle-chatbox">
        <i class="fas fa-comments"></i>
        <span id="unread-count" class="badge"></span>
    </button>
    <input type="text" id="senderUser" value="<?= $_GET['senderUser'] ?>">
    <input type="text" id="receiverUser" value="<?= $_GET['receiverUser'] ?>">
    <div id="chatbox">
        <div id="chatbox-header">
            User <?= $_GET['senderUser'] ?>
            <i class="fas fa-times" id="close-chatbox"></i>
        </div>
        <div id="chatbox-messages"></div>
        <form id="chatbox-input">
            <input type="text" placeholder="Type a message...">
            <!-- <button type="submit">Send</button> -->
        </form>
    </div>
    <script>
        $(document).ready(function() {
            var senderUser = $('#senderUser').val();
            var receiverUser = $('#receiverUser').val();
            var limit = 20;
            var loading = false;
            var hasMoreMessages = true;
            var unreadCount = 0;

            const $chatbox = $('#chatbox');
            const $toggleChatbox = $('#toggle-chatbox');
            const $chatboxInput = $('#chatbox-input');
            const $chatboxMessages = $('#chatbox-messages');
            const $closeChatbox = $('#close-chatbox');

            $toggleChatbox.on('click', function() {
                $chatbox.toggle();
                scrollToBottom();
                readMessage(senderUser, receiverUser)
            });

            $closeChatbox.on('click', function() {
                $chatbox.hide();
            });

            // Enable pusher logging - don't include this in production
            Pusher.logToConsole = true;
            var pusher = new Pusher('fbf5f6ad5ae421e5adb8', {
                cluster: 'ap1'
            });


            var channel = pusher.subscribe('chat-channel');
            channel.bind(`message-receiver-${senderUser}`, function(data) {
                if ($chatbox.is(':hidden')) {
                    unreadCount++;
                    updateUnreadCount();
                } else {
                    readMessage(senderUser, receiverUser);
                }
                
                const $messagerReceiverElement = `
                    <div style ="display: flex;">
                        <div class="message other">${data.content}</div>
                    </div>
                `;
                $chatboxMessages.append($messagerReceiverElement);
                scrollToBottom();
            });

            $('#chatbox-input').on('submit', function(event) {
                event.preventDefault();
                const $input = $(this).find('input');
                const message = $input.val().trim();
                if (message) {
                    // Thêm phần tử HTML tạm thời để hiển thị trạng thái "đang gửi"
                    const $sendingMessageElement = `
                        <div style ="display: flex;">
                            <div class="message self sending">Đang gửi...</div>
                        </div>
                    `;
                    $('#chatbox-messages').append($sendingMessageElement);
                    scrollToBottom();

                    $.post('http://localhost/Chat-Box/message-controller.php', {
                        content: message,
                        sender_id: senderUser,
                        receiver_id: receiverUser
                    }, function(response) {
                        $('.sending').remove();
                        if (response.status === true) {
                            // Cập nhật phần tử HTML tạm thời với nội dung tin nhắn thực
                            const $sentMessageElement = `
                                <div style ="display: flex;">
                                    <div class="message self">${message}</div>
                                </div>
                            `;
                            $('#chatbox-messages').append($sentMessageElement);
                            scrollToBottom();
                        } else {
                            // Thêm thông báo lỗi màu đỏ
                            const $errorMessageElement = $('<div></div>').text(response.message).addClass('message error');
                            $('#chatbox-messages').append($errorMessageElement);
                            scrollToBottom();
                        }
                    }, 'json');

                    $input.val('');
                }
            });

            // Gọi API để lấy 20 tin nhắn mới nhất khi tải trang
            fetchLatestMessages(senderUser, receiverUser, limit);

            $('#send-message-form').on('submit', function(event) {
                event.preventDefault();
                const message = $('#message-input').val();
                if (message.trim() !== '') {
                    $.post('http://localhost/Chat-Box/message-controller.php', {
                        content: message,
                        sender_id: senderUser,
                        receiver_id: receiverUser
                    }, function(response) {
                        if (response.status === true) {
                            const $sentMessageElement = `
                                <div style="display: flex;">
                                    <div class="message self">${message}</div>
                                </div>
                            `;
                            $('#chatbox-messages').append($sentMessageElement);
                            scrollToBottom();
                        } else {
                            const $errorMessageElement = $('<div></div>').text(response.message).addClass('message error');
                            $('#chatbox-messages').append($errorMessageElement);
                            scrollToBottom();
                        }
                    }, 'json');

                    $('#message-input').val('');
                }
            });

            $('#chatbox-messages').on('scroll', function() {
                if ($(this).scrollTop() === 0 && !loading && hasMoreMessages) {
                    loading = true;
                    limit += 20;
                    fetchLatestMessages(senderUser, receiverUser, limit, function() {
                        loading = false;
                    });
                }
            });

            function fetchLatestMessages(sender_id, receiver_id, limit, callback) {
                $.get('http://localhost/Chat-Box/get-messages.php', { 
                    limit: limit, 
                    receiver_id: receiver_id, 
                    sender_id: sender_id
                }, function(response) {
                    if (response.status === true) {
                        $('#chatbox-messages').empty(); // Xóa các tin nhắn hiện tại
                        if (response.messages.length < limit) {
                            hasMoreMessages = false;
                        }
                        let lastDate = null;
                        response.messages.forEach(function(message) {
                            const messageDate = new Date(message.created_at);
                            const messageDateString = formatDate(messageDate);
                            if (lastDate !== messageDateString) {
                                const $dateElement = `
                                    <div class="message-date">
                                     <span class="message-date--content">${messageDateString}</span>
                                    </div>
                                `;
                                $('#chatbox-messages').append($dateElement);
                                lastDate = messageDateString;
                            }
                            const self = message.sender_id == sender_id ? 'self' : 'other';
                            const $messageElement = `
                                <div style="display: flex;">
                                    <div class="message ${self}">${message.content}</div>
                                </div>
                            `;
                            $('#chatbox-messages').append($messageElement); // Thêm tin nhắn mới vào đầu danh sách
                            if (!message.is_read && message.sender_id == receiver_id && message.receiver_id == sender_id) {
                                unreadCount++;
                            }
                        });
                        updateUnreadCount();
                        $('#chatbox-messages').animate({ scrollTop: $('#chatbox-messages').scrollTop() + 500 }, 100);
                    } else {
                        const $errorMessageElement = $('<div></div>').text('Không thể tải tin nhắn').addClass('message error');
                        $('#chatbox-messages').append($errorMessageElement);
                    }
                    if (callback) callback();
                }, 'json');
            }

            function readMessage(sender_id, receiver_id)
            {
                $.post('http://localhost/Chat-Box/read-messages.php', {
                    sender_id: receiver_id,
                    receiver_id: sender_id
                }, function(response) {
                    if (response.status === true) {
                        unreadCount = 0;
                        updateUnreadCount();
                    }
                }, 'json');
            }

            function updateUnreadCount() {
                $('#unread-count').text(unreadCount);
                if (unreadCount > 0) {
                    $('#unread-count').show();
                } else {
                    $('#unread-count').hide();
                }
            }

            function scrollToBottom() {
                const $chatboxMessages = $('#chatbox-messages');
                $chatboxMessages.scrollTop($chatboxMessages[0].scrollHeight);
            }

            function formatDate(date) {
                const today = new Date();
                const yesterday = new Date(today);
                yesterday.setDate(today.getDate() - 1);

                if (date.toDateString() === today.toDateString()) {
                    return "Hôm nay";
                } else if (date.toDateString() === yesterday.toDateString()) {
                    return "Hôm qua";
                } else {
                    return date.toLocaleDateString();
                }
            }
        });
    </script>

</body>

</html>