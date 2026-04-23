<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Intervention démarrée</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 30px; border: 1px solid #e5e7eb; border-radius: 12px;">
        <h2 style="color: #1e3a8a;">Intervention démarrée</h2>
        
        <p>Bonjour,</p>
        
        <p>Le technicien <strong>{{ $technician->name }}</strong> a commencé l'intervention :</p>
        
        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <strong>ID Intervention :</strong> #{{ $intervention->id }}<br>
            <strong>Agence :</strong> {{ $intervention->agency->name }}<br>
            <strong>Date de début :</strong> {{ $intervention->updated_at->format('d/m/Y à H:i') }}
        </div>

        <p style="margin-top: 30px;">
            <a href="{{ url('/admin/interventions/' . $intervention->id) }}" 
               style="background: #1e3a8a; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; display: inline-block;">
                Voir l'intervention
            </a>
        </p>

        <p style="margin-top: 40px; font-size: 13px; color: #64748b;">
            Cordialement,<br>
            <strong>L’équipe Geir</strong>
        </p>
    </div>
</body>
</html>