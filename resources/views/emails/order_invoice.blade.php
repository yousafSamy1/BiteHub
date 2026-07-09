<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your BiteHub Receipt</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        .header { background: #10b981; padding: 40px 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; }
        .body { padding: 40px 30px; }
        .greeting { font-size: 22px; font-weight: 700; color: #1f2937; margin-bottom: 5px; }
        .subtitle { color: #6b7280; font-size: 15px; margin-bottom: 30px; }
        .order-meta { display: flex; justify-content: space-between; margin-bottom: 25px; padding: 15px; background: #f9fafb; border-radius: 10px; }
        .meta-item { flex: 1; }
        .meta-label { font-size: 11px; font-weight: 700; color: #10b981; text-transform: uppercase; margin-bottom: 3px; }
        .meta-value { font-size: 14px; font-weight: 600; color: #111827; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { text-align: left; font-size: 11px; color: #9ca3af; text-transform: uppercase; padding: 10px 5px; border-bottom: 1px solid #e5e7eb; }
        .items-table td { padding: 15px 5px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .item-name { font-weight: 600; color: #111827; }
        .item-price { text-align: right; font-weight: 700; color: #111827; }
        .totals { background: #fdf2f0; border-radius: 10px; padding: 20px; }
        .total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 14px; }
        .total-row.grand { border-top: 1px solid rgba(16, 185, 129, 0.2); margin-top: 10px; padding-top: 12px; font-size: 18px; font-weight: 800; color: #10b981; }
        .footer { margin-top: 40px; text-align: center; padding-top: 25px; border-top: 1px solid #f3f4f6; }
        .legal { font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div style="background-color: #f8f9fa; padding: 20px 0;">
        <div class="wrapper">
            <div class="header">
                <h1>BiteHub</h1>
            </div>
            <div class="body">
                <h2 class="greeting">Order Delivered!</h2>
                <p class="subtitle">Hi {{ $order->customer->user->FullName }}, your order has been successfully delivered. Thank you for your purchase!</p>
                
                <div class="order-meta">
                    <div class="meta-item">
                        <div class="meta-label">Order ID</div>
                        <div class="meta-value">#{{ $order->KitchenOrderNumber ?? $order->OrderID }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Date</div>
                        <div class="meta-value">{{ now()->format('d M Y') }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Payment</div>
                        <div class="meta-value">{{ $order->payment->Method ?? 'N/A' }}</div>
                    </div>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->menuItems as $item)
                        <tr>
                            <td class="item-name">{{ $item->ItemName }}</td>
                            <td style="text-align: center;">{{ $item->pivot->Quantity }}</td>
                            <td class="item-price">{{ number_format($item->pivot->Quantity * ($item->DiscountPrice ?? $item->ItemPrice), 2) }} <small>EGP</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="totals">
                    <div class="total-row">
                        <span>Order Type</span>
                        <span style="font-weight: 600;">{{ $order->OrderType }}</span>
                    </div>
                    <div class="total-row grand">
                        <span>Total Paid</span>
                        <span>{{ number_format($order->TotalPrice, 2) }} EGP</span>
                    </div>
                </div>

                <div class="footer">
                    <p class="legal">
                        &copy; {{ date('Y') }} BiteHub — Egypt's #1 Home Food Platform<br>
                        Thank you for supporting real home cooks!
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
