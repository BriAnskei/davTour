<!DOCTYPE html>
<html>
<head>
    <title>Payment Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1a3a2a; padding-bottom: 10px; }
        .header h1 { color: #1a3a2a; margin: 0; }
        .header p { margin: 5px 0; color: #666; font-size: 14px; }
        .summary { margin-bottom: 20px; background: #f9f9f9; padding: 15px; border-radius: 8px; }
        .summary table { width: 100%; }
        .summary td { font-size: 13px; }
        .label { font-weight: bold; color: #1a3a2a; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th { background: #1a3a2a; color: white; text-align: left; padding: 10px; font-size: 12px; }
        table.data td { padding: 10px; border-bottom: 1px solid #eee; font-size: 11px; }
        .status { padding: 3px 8px; border-radius: 10px; font-weight: bold; font-size: 10px; }
        .status-completed { background: #d6ece0; color: #1a3a2a; }
        .status-pending { background: #f9e3bb; color: #c9872a; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
        .total-row { background: #f5f1eb; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DavaoTours Payment Report</h1>
        <p>Generated on {{ $date }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Filtered By Tour:</td>
                <td>{{ $filters['tour'] }}</td>
                <td class="label">Status:</td>
                <td>{{ ucfirst($filters['status']) }}</td>
            </tr>
            <tr>
                <td class="label">Report Date/Range:</td>
                <td>{{ $filters['date'] }}</td>
                <td class="label" style="font-size: 16px;">Total Revenue:</td>
                <td style="font-size: 16px; font-weight: bold; color: #c9872a;">₱{{ number_format($total, 2) }}</td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Tour</th>
                <th>Schedule Date</th>
                <th>Pax</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date Paid</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>#{{ $payment->id }}</td>
                <td>{{ $payment->booking->user->name ?? '—' }}</td>
                <td>{{ $payment->booking->tourSchedule->tour->name ?? '—' }}</td>
                <td>{{ $payment->booking->tourSchedule ? \Carbon\Carbon::parse($payment->booking->tourSchedule->date)->format('M d, Y') : '—' }}</td>
                <td>{{ $payment->booking->p_count ?? 0 }}</td>
                <td style="font-weight: bold;">₱{{ number_format($payment->amount, 2) }}</td>
                <td>
                    <span class="status status-{{ $payment->payment_status }}">
                        {{ ucfirst($payment->payment_status) }}
                    </span>
                </td>
                <td>{{ $payment->created_at->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" style="text-align: right; padding-right: 20px;">TOTAL COMPLETED REVENUE:</td>
                <td colspan="3">₱{{ number_format($total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        © {{ date('Y') }} DavaoTours Admin Panel — Official Payment Record
    </div>
</body>
</html>