<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #333;
        }
        .content {
            font-size: 16px;
            line-height: 1.6;
        }
        .content p {
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            font-size: 14px;
            text-align: center;
            margin-top: 30px;
            color: #888;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Password Reset Request</h1>
    </div>

    <div class="content">
        <p>Dear {{ $user->name }},</p>
        <p>We received a request to reset your password. Please click the button below to reset your password:</p>
        <a href="{{ url('new-password/'.$token) }}" class="button">Reset Your Password</a>
        <p>If you did not request a password reset, please ignore this email.</p>
    </div>

    <div class="footer">
        <p>Regards,</p>
        <p><strong>Xmeet Team</strong></p>
        <p>If you have any questions, feel free to <a href="https://xmeet.algohat.com">contact us</a>.</p>
    </div>
</div>

</body>
</html>
