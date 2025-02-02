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
    <p><strong>Von:</strong> Name: {{ $sender->name ?? 'N/A' }}; <strong>Geschlecht:</strong> {{ $sender->gender ?? 'N/A' }}; <strong>Age:</strong> {{ $sender->age ?? 'N/A' }}; <strong>Interessen:</strong> {{ $sender->interest ?? 'N/A' }}; <strong>Zipcode:</strong> {{ substr($sender->post_code ?? 'N/A', 0, 2) }}xx</p>

    <p><strong>An:</strong> Name: {{ $receiver->name ?? 'N/A' }}; <strong>Geschlecht:</strong> {{ $receiver->gender ?? 'N/A' }}; <strong>Age:</strong> {{ $receiver->age ?? 'N/A' }}; <strong>Interessen:</strong> {{ $receiver->interest ?? 'N/A' }}; <strong>Zipcode:</strong> {{ substr($receiver->post_code ?? 'N/A', 0, 2) }}xx</p>

    <hr>
    <p><strong>Message:</strong></p>
    <p>{{ $chat_text ?? 'N/A' }}</p>
    <hr>
    <div style="margin: 20px 0; display: flex; gap: 10px;">
        <!-- Block User Button -->
        <a href="{{ route('block.confirm', ['sender_identifier' => $sender->identifier, 'receiver_identifier' => $receiver->identifier]) }}"
           style="display: inline-block; padding: 5px 10px; background-color: #ff4d4f; color: #fff; text-decoration: none;
              font-weight: bold; border-radius: 3px; text-align: center; font-size: 14px; border: 1px solid #ff4d4f;"
           onmouseover="this.style.backgroundColor='#fff'; this.style.color='#ff4d4f';"
           onmouseout="this.style.backgroundColor='#ff4d4f'; this.style.color='#fff';">
            Block User
        </a>

        <!-- Kontakt Button -->
        <a href="#"
           style="display: inline-block; padding: 5px 10px; background-color: #007bff; color: #fff; text-decoration: none;
              font-weight: bold; border-radius: 3px; text-align: center; font-size: 14px; border: 1px solid #007bff;"
           onmouseover="this.style.backgroundColor='#fff'; this.style.color='#007bff';"
           onmouseout="this.style.backgroundColor='#007bff'; this.style.color='#fff';">
            Kontakt
        </a>
    </div>
    <div class="footer" style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px; font-size: 14px; color: #555;">
        <p style="margin: 0; font-size: 16px; font-weight: bold; color: #333;">Thank you,</p>
        <p style="margin: 5px 0 20px 0; font-size: 16px; font-weight: bold; color: #333;">Xmeet</p>
        <div class="footer-links" style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
            <a href="https://xmeet.algohat.com/data-protection"
               style="text-decoration: none; color: #007bff; font-weight: bold; transition: color 0.3s;">
                Datenschutzerklärung
            </a>
            <a href="https://xmeet.algohat.com/terms-and-condition"
               style="text-decoration: none; color: #007bff; font-weight: bold; transition: color 0.3s;">
                AGB
            </a>
            <a href="https://xmeet.algohat.com/cookies"
               style="text-decoration: none; color: #007bff; font-weight: bold; transition: color 0.3s;">
                Cookies
            </a>
            <a href="https://xmeet.algohat.com/legal-notice"
               style="text-decoration: none; color: #007bff; font-weight: bold; transition: color 0.3s;">
                Impressum
            </a>
            <a href="https://xmeet.algohat.com/contact"
               style="text-decoration: none; color: #007bff; font-weight: bold; transition: color 0.3s;">
                Kontakt
            </a>
        </div>
        <div style="margin-top: 20px; font-size: 12px; color: #888; text-align: center;">
            <p>&copy; 2025 Xmeet, All Rights Reserved</p>
        </div>
    </div>
</div>

</body>
</html>
