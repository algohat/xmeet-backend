<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body { font-family: 'Arial', sans-serif; color: #333; margin: 0; padding: 20px; background: #f7f7f7; }
        .invoice-container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; margin-bottom: 20px; }
        .header img { max-width: 120px; }
        .header h2 { margin-top: 10px; color: #333; }
        .invoice-details { width: 100%; margin-bottom: 20px; }
        .invoice-details p { margin: 5px 0; font-size: 14px; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .details-table th { background: #f5f5f5; font-weight: bold; }
        .total-section { font-size: 18px; font-weight: bold; text-align: right; margin-bottom: 20px; }
        .footer { text-align: center; font-size: 12px; color: #666; margin-top: 20px; }
    </style>
</head>
<body>
<div class="invoice-container">
    <!-- Header -->
    <div class="header">
        <h2>Invoice</h2>
        <p>Date: <strong>{{ $date }}</strong></p>
        <p>Transaction ID: <strong>{{ $transaction_id }}</strong></p>
    </div>

    <!-- User Details -->
    <h3>Billing Information</h3>
    <div class="invoice-details">
        <p><strong>Name:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
    </div>

    <!-- Subscription Details -->
    <h3>Subscription Details</h3>
    <table class="details-table">
        <tr>
            <th>Package</th>
            <th>Price</th>
            <th>Validity</th>
            <th>Duration</th>
            <th>Payment Method</th>
        </tr>
        <tr>
            <td>{{ $package->name }}</td>
            <td>${{ number_format($package->price, 2) }}</td>
            <td>{{ $package->validity }} {{ ucfirst($package->validity_type) }}</td>
            <td>
                <small>Start: {{ $start_time }} </small><br>
                <small>End: {{ $end_time }}</small>
            </td>
            <td>{{ $payment_method }}</td>
        </tr>
    </table>

    <!-- Total -->
    <div class="total-section">
        Total: ${{ number_format($package->price, 2) }}
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your purchase! If you have any questions, contact us at https://xmeet.algohat.com</p>
    </div>
</div>
</body>
</html>
