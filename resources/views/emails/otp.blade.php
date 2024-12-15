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
    <p>{{ $mailData['body'] }}</p>

    <br/><br/><br/><br/><br/>
    <b>Thanks &amp; Regards,</b><br>
    <p>{{ $mailData['team'] }}</p>
    <a href="{{ $mailData['website'] }}">Kubera Scheme</a>
</body>
</html>
