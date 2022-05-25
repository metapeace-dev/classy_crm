<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>Commission Report - {{ $designer }}</title>
    <style>

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #0087C3;
            text-decoration: none;
        }

        body {
            position: relative;
            width: 100%;
            height: auto;
            margin: 0 auto;
            color: #555555;
            background: #FFFFFF;
            font-size: 12px;
            font-family: 'DejaVu Sans', sans-serif;
        }


        header {
            padding: 5px 0;
            border-bottom: 2px solid #666666;
        }

        #logo img {
            height: 50px;
            width: 200px;
            margin-bottom: 15px;
        }

        #details {
            margin-bottom: 50px;
        }

        #client .to {
            color: #777777;
        }


        #summary h4 {
            color: #0087C3;
            font-size: 1.5em;
            line-height: 1em;
            font-weight: normal;
            margin: 0 0 10px 0;
        }

        #summary .date {
            font-size: 1.2em;
            color: #777777;
        }

        header table td, header table th{
            background: #FFFFFF !important;
        }

        table {
            width: 100%;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 5px 10px 7px 10px;
            background: #EEEEEE;
            text-align: center;
            border-bottom: 1px solid #FFFFFF;
        }

        table td {
            text-align: right;
            font-size: 0.9em;
            font-weight: normal;
            margin: 0 0 0 0;
        }

        table th {
            color: #FFFFFF;
            font-size: 1.0em;
            background: #0087C3;
            font-weight: normal;
        }

        table .desc {
            text-align: left;
        }


        table .total {
            background: #57B223;
            color: #FFFFFF;
        }

        table td.unit,

        table td.unit{
            width: 11%;
            text-align: center;
        }

        table td.desc{
            width: 13%;
            /*white-space: nowrap;*/
            text-align: center;
        }

        table tr.tax .desc {
            text-align: right;
            color: #1BA39C;
        }
        table tr.discount .desc {
            text-align: right;
            color: #E43A45;
        }
        table tr.subtotal .desc {
            text-align: right;
            color: #1d0707;
        }
        table tbody tr:last-child td {
            border: none;
        }

        table tfoot td {
            padding: 10px 10px 20px 10px;
            background: #FFFFFF;
            color: #0087C3;
            border-bottom: none;
            font-size: 1.0em;
            white-space: nowrap;
            border-bottom: 1px solid #AAAAAA;
        }

        table tfoot tr:first-child td {
            border-top: none;
        }

        table tfoot tr td:first-child {
            border: none;
        }


        #notices .notice {
            font-size: 1.2em;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #AAAAAA;
            padding: 8px 0;
            text-align: center;
        }

        table.billing td {
            background-color: #fff;
        }

        table td div#summaryd_to {
            text-align: left;
        }


        .logo{
            width: 200px;
            text-align: center;
        }
    </style>
</head>
<body>
<header class="clearfix">
    <table cellpadding="0" cellspacing="0" style="margin-bottom: 0px">
        <tr>
            <td width="30%" style="padding-left: 0px;">
                <img src="{{ public_path('/img/worksuite-logo.png') }}" alt="home" class="logo"/>
            </td>
            <td width="40%" style="text-align: center !important;">
                <div id="sheetTitle">
                    <h1>Commission Report</h1>
                </div>

            </td>
            <td width="30%">
                <div id="sheetTitle">
                </div>
            </td>
        </tr>
    </table>
</header>
<main>
    <div id="details" class="clearfix">
        <div id="summary" style="margin-top: 15px">
            <h4>Designer: {{ $designer }}</h4>
            <h4 class="date">Pay Period: {{ $pay_start_date. ' ~ '. $pay_end_date }}</h4>
        </div>

        <h2>Commissions</h2>
        <table border="0" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
                <th>Client</th>
                <th>Project</th>
                <th>Sales Price</th>
                <th>Payments Received</th>
                <th>Commission Paid</th>
                <th>Balance Due</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>

            @foreach($exportData as $commission)
                <tr style="page-break-inside: avoid;">
                    <td class="desc">{{ $commission['client'] ?? '' }}</td>
                    <td class="desc">{{ $commission['project'] ?? '' }}</td>
                    <td class="unit">{{ $commission['sales_price'] ?? '' }}</td>
                    <td class="unit">{{ $commission['payments_received'] ?? '' }}</td>
                    <td class="unit">{{ $commission['commission_paid'] ?? '' }}</td>
                    <td class="unit">{{ $commission['commission_due'] ?? '' }}</td>
                    <td class="unit">{{ $commission['commission_amount'] ?? '' }}</td>
                </tr>
            @endforeach
{{--            <tr style="page-break-inside: avoid;" class="subtotal">--}}
{{--                <td class="desc" colspan="6">Total Commission Amount</td>--}}
{{--                <td class="unit">{{'$'.number_format($total_amount, 2, '.', ',')}}</td>--}}
{{--            </tr>--}}
            </tbody>
            <tfoot>
            <tr dontbreak="true">
                <td colspan="6">Total Sales Price</td>
                <td style="text-align: center">{{'$'.number_format($total_sales_price, 2, '.', ',')}}</td>
            </tr>
            <tr dontbreak="true">
                <td colspan="6">Total Payments Received</td>
                <td style="text-align: center">{{'$'.number_format($total_payments_received, 2, '.', ',')}}</td>
            </tr>
            <tr dontbreak="true">
                <td colspan="6">Total Commission Paid</td>
                <td style="text-align: center">{{'$'.number_format($total_commission_paid, 2, '.', ',')}}</td>
            </tr>
            <tr dontbreak="true">
                <td colspan="6">Total Balance Due</td>
                <td style="text-align: center">{{'$'.number_format($total_balance_due, 2, '.', ',')}}</td>
            </tr>
            <tr dontbreak="true">
                <td colspan="6">Total Commission Amount</td>
                <td style="text-align: center">{{'$'.number_format($total_amount, 2, '.', ',')}}</td>
            </tr>
            </tfoot>
        </table>
    </div>
</main>
</body>
</html>