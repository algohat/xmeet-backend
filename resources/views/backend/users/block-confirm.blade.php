<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Block</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 450px;
            padding: 30px;
            text-align: center;
        }

        h1 {
            font-size: 22px;
            color: #444;
            margin-bottom: 20px;
        }

        .confirm-block-btn {
            padding: 12px 25px;
            background-color: #ff4d4f;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            margin-bottom: 20px;
        }

        .confirm-block-btn:hover {
            background-color: #e03a3a;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            font-size: 14px;
            color: #444;
            display: block;
            margin-bottom: 5px;
        }

        .form-group input[type="text"] {
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input[type="text"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .cancel-link {
            display: block;
            margin-top: 20px;
            font-size: 14px;
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s;
        }

        .cancel-link:hover {
            color: #0056b3;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>

<div class="container">
    <p><strong>Von:</strong> Name: {{ $sender->name ?? 'N/A' }}; <strong>Geschlecht:</strong> {{ $sender->gender ?? 'N/A' }}; <strong>Age:</strong> {{ $sender->age ?? 'N/A' }}; <strong>Interessen:</strong> {{ $sender->interest ?? 'N/A' }}; <strong>Zipcode:</strong> {{ substr($sender->post_code ?? 'N/A', 0, 2) }}xx</p>

    <h1>Are you sure you want to block this user?</h1>

    <form action="{{ route('user.block', ['sender_identifier' => $sender_identifier, 'receiver_identifier' => $receiver_identifier]) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="reason">Reason for Blocking</label>
            <input type="text" id="reason" name="reason" placeholder="Optional: Provide a reason for blocking">
        </div>

        <button type="submit" class="confirm-block-btn">Yes, Block User</button>
    </form>

    <a href="https://xmeet.algohat.com" class="cancel-link">Cancel</a>

    <div class="footer">
        <p>&copy; 2025 Xmeet. All rights reserved.</p>
    </div>
</div>

<script>
    document.querySelector('.confirm-block-btn').addEventListener('click', function(event) {
        if (!confirm('Are you sure you want to block this user?')) {
            event.preventDefault();
        }
    });
</script>

</body>
</html>
