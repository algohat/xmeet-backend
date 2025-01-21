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
<div class="container" style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <p><strong>Name:</strong> {{ $sender->name ?? 'N/A' }}; <strong>Geschlecht:</strong> {{ $sender->gender ?? 'N/A' }}; <strong>Age:</strong> {{ $sender->age ?? 'N/A' }}; <strong>Zipcode:</strong> {{ $sender->post_code ?? 'N/A' }}</p>
    <hr>
    <p><strong>Message:</strong></p>
    <p>{{ $chat_text ?? 'N/A' }}</p>
    <hr>
    <div style="margin: 20px 0; display: flex; gap: 10px;">
        <!-- Block User Button -->
        <a href="{{ $block_user_url ?? '#' }}"
           style="display: inline-block; padding: 5px 10px; background-color: #ff4d4f; color: #fff; text-decoration: none;
              font-weight: bold; border-radius: 3px; text-align: center; font-size: 14px; border: 1px solid #ff4d4f;"
           onmouseover="this.style.backgroundColor='#fff'; this.style.color='#ff4d4f';"
           onmouseout="this.style.backgroundColor='#ff4d4f'; this.style.color='#fff';">
            Block User
        </a>

        <!-- Kontakt Button -->
        <a href="https://xmeet.algohat.com/contact"
           style="display: inline-block; padding: 5px 10px; background-color: #007bff; color: #fff; text-decoration: none;
              font-weight: bold; border-radius: 3px; text-align: center; font-size: 14px; border: 1px solid #007bff;"
           onmouseover="this.style.backgroundColor='#fff'; this.style.color='#007bff';"
           onmouseout="this.style.backgroundColor='#007bff'; this.style.color='#fff';">
            Kontakt
        </a>
    </div>
    <div class="footer" style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
        <p>Thank you,</p>
        <p>Xmeet</p>
    </div>
</div>

</body>
</html>
