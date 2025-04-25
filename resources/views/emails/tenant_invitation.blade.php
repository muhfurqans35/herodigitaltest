<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Undangan Bergabung ke Tenant</title>
</head>
<body>
    <h2>Halo!</h2>
    <p>Kamu diundang untuk bergabung ke tenant <strong>{{ $tenantName }}</strong>.</p>
    <p>Silakan klik tautan di bawah ini untuk menerima undangan:</p>
    <p><a href="{{ $invitationLink }}">{{ $invitationLink }}</a></p>
    <p>Jika kamu tidak mengenali undangan ini, abaikan saja email ini.</p>
</body>
</html>
