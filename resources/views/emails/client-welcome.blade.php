<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bienvenue chez Geir</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <h2 style="color: #1e3a8a;">Bienvenue chez <strong>Geir</strong> !</h2>
        
        <p>Bonjour <strong>{{ $client->name }}</strong>,</p>
        
        <p>Votre compte client a été créé avec succès.</p>
        
        <p style="background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #1e3a8a;">
            <strong>Identifiants de connexion :</strong><br>
            <strong>Email :</strong> {{ $client->email }}<br>
            <strong>Mot de passe :</strong> <code style="background:#e2e8f0; padding:2px 6px; border-radius:3px;">{{ $password }}</code>
        </p>

        <p>Nous vous recommandons de changer votre mot de passe dès votre première connexion pour plus de sécurité.</p>

        <p style="margin-top: 30px;">
            <a href="{{ url('/login') }}" 
               style="background: #1e3a8a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                Se connecter à Geir
            </a>
        </p>

        <p style="margin-top: 30px; font-size: 13px; color: #666;">
            Cordialement,<br>
            <strong>L’équipe Geir</strong>
        </p>
    </div>
</body>
</html>
