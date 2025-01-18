<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body>
<h1>Password Reset Notification</h1>
<p>Dear {{ $user->name }},</p>
<p>Your password has been successfully reset. Below is your new password:</p>
<p><strong>{{ $password }}</strong></p>
<p>Please log in using this password and change it immediately for security purposes.</p>
<br>
<p>Regards,</p>
<p><strong>Xmeet</strong></p>
</body>
</html>
