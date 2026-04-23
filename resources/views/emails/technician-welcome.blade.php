<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bienvenue chez Geir</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 30px; border: 1px solid #e5e7eb; border-radius: 12px;">
        <h2 style="color: #1e3a8a; margin-bottom: 20px;">Bienvenue chez <strong>Geir</strong> !</h2>
        
        <p>Bonjour <strong>{{ $technician->name }}</strong>,</p>
        
        <p>Votre compte technicien a été créé avec succès.</p>
        
        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 5px solid #1e3a8a; margin: 20px 0;">
            <strong>Vos identifiants de connexion :</strong><br><br>
            <strong>Email :</strong> {{ $technician->email }}<br>
            <strong>Mot de passe :</strong> <code style="background:#e2e8f0; padding: 4px 8px; border-radius: 4px;">{{ $password }}</code>
        </div>

        <p>Nous vous recommandons de changer votre mot de passe dès votre première connexion pour plus de sécurité.</p>

        <p style="margin-top: 30px;">
            <a href="{{ url('/login') }}" 
               style="background: #1e3a8a; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 600;">
                Se connecter à Geir
            </a>
        </p>

        <p style="margin-top: 40px; font-size: 13px; color: #64748b;">
            Cordialement,<br>
            <strong>L’équipe Geir</strong>
        </p>
    </div>
</body>
</html>