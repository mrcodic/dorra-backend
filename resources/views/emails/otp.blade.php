<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Code</title>
</head>
<body>
<h2>Your OTP Code</h2>
<p>Hello,</p>
<p>Your One-Time Password (OTP) is: <strong>{{ $otp }}</strong></p>
<p>This OTP is valid for 5 minutes.</p>
<p>If you did not request this, please ignore this email.</p>
<br>
<p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
