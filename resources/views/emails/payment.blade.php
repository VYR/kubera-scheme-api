<!DOCTYPE html>
<html>
<head>
    <title>Email</title>
</head>
<body>
    <img src="{{ $mailData['logo'] }}" alt="Kubera Scheme"  width="90" height="95" />
    <br/><br/><br/>
    <b>{{ $mailData['salutation'] }},</b>
    <br/>
    <p>Your payment has been received successfully. Below are the payment details</p>
    <br/><br/>
    <table role="presentation" border="0" width="100%" cellspacing="2">
        <tr>
            <th align="left">Payment For</th><td>{{ $mailData['details']['paymentForLabel'] }}</td>
        </tr>
        <tr>
            <th align="left">Name</th><td>{{ $mailData['details']['name'] }}</td>
        </tr>
        <tr>
            <th align="left">Email ID</th><td>{{ $mailData['details']['email'] }}</td>
        </tr>
        <tr>
            <th align="left">Phone Number</th><td>{{ $mailData['details']['phone'] }}</td>
        </tr>
        <tr>
            <th align="left">Amount</th><td>Rs.{{ $mailData['details']['amount_paid'] }}</td>
        </tr>
        <tr>
            <th align="left">Transaction No#</th><td>{{ $mailData['details']['txnNo'] }}</td>
        </tr>
    </table>

    <br/><br/><br/><br/><br/>
    <b>Thanks &amp; Regards,</b><br>
    <p>{{ $mailData['team'] }}</p>
    <a href="{{ $mailData['website'] }}">Kubera Scheme</a>
</body>
</html>
