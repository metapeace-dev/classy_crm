<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{$title}}</title>
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
            /*width: 11%;*/
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
                    <h1>{{$title}}</h1>
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
                @foreach($exportData[0] as $head)
                <th class="unit">{{$head}}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            <?php $index = 0; ?>
            @foreach($exportData as $data)
                    @if($index > 0)
                        <tr>
                        @foreach($exportData[0] as $head)
                            <?php
                                if($head == 'Created On')
                                    $head = 'created_at';
                            ?>
                            <td class="unit">{{ $data[strtolower($head)] ?? '' }}</td>
                        @endforeach
                        </tr>
                    @endif
                <?php $index++; ?>
            @endforeach
            </tbody>
        </table>
    </div>
</main>
</body>
</html>