<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>Commission Report - {{ $project->designer->name }}</title>
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
            height: 55px;
            margin-bottom: 15px;
        }

        #details {
            margin-bottom: 50px;
        }

        #client {
            padding-left: 6px;
            float: left;
        }

        #client .to {
            color: #777777;
        }

        h2.name {
            font-size: 1.2em;
            font-weight: normal;
            margin: 0;
        }

        #summary {

        }

        #summary h1 {
            color: #0087C3;
            font-size: 2.4em;
            line-height: 1em;
            font-weight: normal;
            margin: 0 0 10px 0;
        }

        #summary .date {
            font-size: 1.1em;
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

        table th {
            white-space: nowrap;
            font-weight: normal;
        }

        table td {
            text-align: right;
        }

        table td.desc h3, table td.qty h3 {
            color: #57B223;
            font-size: 1.2em;
            font-weight: normal;
            margin: 0 0 0 0;
        }

        table .no {
            color: #FFFFFF;
            font-size: 1.6em;
            background: #57B223;
            width: 10%;
        }

        table .desc {
            text-align: left;
        }

        table .unit {
            background: #DDDDDD;
        }


        table .total {
            background: #57B223;
            color: #FFFFFF;
        }

        table td.unit,
        table td.qty,
        table td.total
        {
            font-size: 1.2em;
            text-align: center;
        }

        table td.unit{
            width: 35%;
        }

        table td.desc{
            width: 45%;
        }

        table td.qty{
            width: 5%;
        }

        .status {
            margin-top: 15px;
            padding: 1px 8px 5px;
            font-size: 1.3em;
            width: 80px;
            color: #fff;
            float: right;
            text-align: center;
            display: inline-block;
        }

        .status.unpaid {
            background-color: #E7505A;
        }
        .status.paid {
            background-color: #26C281;
        }
        .status.cancelled {
            background-color: #95A5A6;
        }
        .status.error {
            background-color: #F4D03F;
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
            border-bottom: none;
            font-size: 1.2em;
            white-space: nowrap;
            border-bottom: 1px solid #AAAAAA;
        }

        table tfoot tr:first-child td {
            border-top: none;
        }

        table tfoot tr td:first-child {
            border: none;
        }

        #thanks {
            font-size: 2em;
            margin-bottom: 50px;
        }

        #notices {
            padding-left: 6px;
            border-left: 6px solid #0087C3;
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

        #notes{
            color: #767676;
            font-size: 11px;
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
            <h3>Designer: {{ ucfirst($project->designer->name) }}</h3>
            <h3 class="">Client: {{ $project->client->full_name }}</h3>
            <h3 class="">Project: {{ ucfirst($project->project_name) }}</h3>
        </div>
        <h1>Payments</h1>
        <table border="0" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
                <th class="no">#</th>
                <th class="desc">Payment Type</th>
                <th class="desc">Paid On</th>
                <th class="desc">Status</th>
                <th class="unit">Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($payments as $payment)
                    <tr style="page-break-inside: avoid;">
                        <td class="no">{{ $payment->id }}</td>
                        <td class="desc"><h3>{{ ucfirst($payment->payment_type) }}</h3></td>
                        <td class="desc"><h3>{{ $payment->paid_on->format($global->date_format) }}</h3></td>
                        <td class="desc"><h3>{{ ucfirst($payment->status) }}</h3></td>
                        <td class="unit">{{'$'.number_format($payment->amount, 2, '.', ',')}}</td>
                    </tr>
            @endforeach
            <tr style="page-break-inside: avoid;" class="subtotal">
                <td class="no">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                <td class="desc">Paid Amount</td>
                <td class="unit">{{'$'.number_format($completes->paidPayments, 2, '.', ',')}}</td>
            </tr>
            </tbody>
        </table>

        <h1>Commissions</h1>
        <table border="0" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
                <th class="no">#</th>
                <th class="desc">Pay Start Date</th>
                <th class="desc">Pay End Date</th>
                <th class="desc">Status</th>
                <th class="unit">Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($commissions as $commission)
                <tr style="page-break-inside: avoid;">
                    <td class="no">{{ $commission->id }}</td>
                    <td class="desc"><h3>{{ $commission->pay_start_date->format($global->date_format) }}</h3></td>
                    <td class="desc"><h3>{{ $commission->pay_end_date->format($global->date_format) }}</h3></td>
                    <td class="desc"><h3>{{ ucfirst($commission->status) }}</h3></td>
                    <td class="unit">{{'$'.number_format($commission->amount, 2, '.', ',')}}</td>
                </tr>
            @endforeach
            <tr style="page-break-inside: avoid;" class="subtotal">
                <td class="no">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                <td class="desc">Paid Amount</td>
                <td class="unit">{{'$'.number_format($completes->paidCommissions, 2, '.', ',')}}</td>
            </tr>
            </tbody>
            <tfoot>
            <tr dontbreak="true">
                <td colspan="4">Sales Price</td>
                <td style="text-align: center">{{'$'.number_format($project->sales_price, 2, '.', ',')}}</td>
            </tr>
            <tr dontbreak="true">
                <td colspan="4">Project Cost</td>
                <td style="text-align: center">{{'$'.number_format($completes->paidPayments, 2, '.', ',')}}</td>
            </tr>
            <?php
            $due_amount = $completes->paidPayments * $project->commission / 100 - $completes->paidCommissions;
            if($due_amount < 0){
                $due_amount = '$0.00';
            }
            else{
                $due_amount = '$'.number_format($due_amount, 2, '.', ',');
            }
            ?>
            <tr dontbreak="true">
                <td colspan="4">Balance Due</td>
                <td style="text-align: center">{{ $due_amount }}</td>
            </tr>
            </tfoot>
        </table>
    </div>
</main>
</body>
</html>