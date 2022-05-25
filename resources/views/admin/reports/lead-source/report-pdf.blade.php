<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>Lead Summary Report</title>
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
            border-bottom: 1px solid #555555;
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
            border: 1px solid #555555;
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
        /*table tbody tr:last-child td {*/
        /*    border: none;*/
        /*}*/

        table tfoot td {
            padding: 10px 10px 20px 10px;
            background: #FFFFFF;
            color: #0087C3;
            border-bottom: none;
            font-size: 1.0em;
            white-space: nowrap;
            border-bottom: 1px solid #AAAAAA;
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
    <table border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 0px; border: none">
        <tr style="border: none">
            <td width="30%" style="padding-left: 0px; border: none">
                <img src="{{ public_path('/img/worksuite-logo.png') }}" alt="home" class="logo"/>
            </td>
            <td width="40%" style="text-align: center !important; border: none">
                <div id="sheetTitle">
                    <h1>Lead Summary Report</h1>
                </div>

            </td>
            <td width="30%" style="border: none">
                <div id="sheetTitle">
                </div>
            </td>
        </tr>
    </table>
</header>
<main>
    <div id="details" class="clearfix">
        <div id="summary" style="margin-top: 15px">
            <h3>Period : {{ $startDate }} ~ {{$endDate}}</h3>
        </div>

        <table border="1" cellspacing="0" cellpadding="0" style="margin-top: 15px">
            <thead>
            <tr>
                <th class="unit">ID</th>
                <th class="unit">Name</th>
                <th class="unit">Description</th>
                <th class="unit">Project Count</th>
                <th class="unit">Sold</th>
                <th class="unit">Amount</th>
                <th class="unit">Average Amount</th>
            </tr>
            </thead>
            <tbody>
            <?php
                $count_total  = 0; $sold_total = 0; $amount_total = 0; $avg_total = 0;
            ?>
            @foreach($projects as $project)
                    <tr style="page-break-inside: avoid;">
                        <td class="desc">{{ $project->id }}</td>
                        <td class="desc">{{ ucfirst($project->name) }}</td>
                        <td class="desc">{{ $project->descrition }}</td>
                        <td class="unit">{{ $project->project_count ?? 0 }}</td>
                        <td class="unit">{{ $project->sold ?? 0 }}</td>
                        <td class="unit">{{'$'.number_format($project->amount ?? 0, 2, '.', ',')}}</td>
                        <td class="unit">{{'$'.number_format($project->average_amount ?? 0, 2, '.', ',')}}</td>
                    </tr>
                    <?php
                    $count_total += $project->project_count ?? 0;
                    $sold_total += $project->sold ?? 0;
                    $amount_total += !empty($project->amount) ? $project->amount : 0;
                    $avg_total += !empty($project->average_amount) ? $project->average_amount : 0;
                    ?>
            @endforeach
            <tr style="page-break-inside: avoid;" class="subtotal">
                <td class="unit" colspan="3"><h3>Grand Total</h3></td>
                <td class="unit">{{$count_total}}</td>
                <td class="unit">{{$sold_total}}</td>
                <td class="unit">{{'$'.number_format($amount_total, 2, '.', ',')}}</td>
                <td class="unit">{{'$'.number_format($avg_total/count($projects), 2, '.', ',')}}</td>
            </tr>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>