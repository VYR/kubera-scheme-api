<!DOCTYPE html>
<html>
<head>
    <title>Generate PDF using Laravel TCPDF</title>
    <style>
        table th{
            width:50%;
        }
    </style>
</head>
<body>
    <h1 style="color:red;" align="center">Save Gold Scheme</h1>

    <table width="100%" cellspacing="2" cellpadding="3">
        <tr>
            <th width="20%">Payment For:</th>
            <td width="80%">{!! $description !!}</td>
        </tr>
        <tr>
            <th width="20%">Payment ID:</th>
            <td color="blue">{!! $paymentId !!}</td>
        </tr>
        <tr>
            <th width="20%">Payment Mode:</th>
            <td>{!! $paymentMode !!}</td>
        </tr>
        <tr>
            <th width="20%">Email ID:</th>
            <td>{!! $email !!}</td>
        </tr>
        <tr>
            <th width="20%">Contact:</th>
            <td>{!! $phone !!}</td>
        </tr>
    </table>

    <br>

    <p align="center">Thank you.</p>
</body>
</html>
