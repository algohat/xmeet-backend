<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New First-Time Message Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #444;
            font-size: 20px;
            margin-bottom: 20px;
        }
        p {
            margin: 10px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>New First-Time Message Notification</h1>
    <p><strong>Sender:</strong> {{ $senderName }} (ID: {{ $senderId }})</p>
    <p><strong>Receiver:</strong> {{ $receiverName }} (ID: {{ $receiverId }})</p>
    <p><strong>Message Content:</strong></p>
    <blockquote>
        {{ $message }}
    </blockquote>
    <p>This is a notification that the above sender has initiated a conversation with the receiver for the first time.</p>
    <div class="footer">
        <p>Thank you,</p>
        <p>Xmeet</p>
    </div>
</div>
</body>
</html>
