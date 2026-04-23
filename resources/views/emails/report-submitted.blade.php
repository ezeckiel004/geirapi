<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport soumis</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 30px; border: 1px solid #e5e7eb; border-radius: 12px;">
        <h2 style="color: #1e3a8a;">
            @if($recipient === 'admin')
                Nouveau rapport soumis
            @else
                Votre rapport d'intervention est disponible
            @endif
        </h2>
        
        <p>Bonjour,</p>
        
        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <strong>Intervention :</strong> #{{ $intervention->id }}<br>
            <strong>Agence :</strong> {{ $intervention->agency->name }}<br>
            <strong>Technicien :</strong> {{ $report->technician->name }}<br>
            <strong>Date de soumission :</strong> {{ $report->submitted_at->format('d/m/Y à H:i') }}
        </div>

        @if($recipient === 'admin')
            <p style="margin-top: 30px;">
                <a href="{{ url('/admin/reports/' . $report->id) }}" 
                   style="background: #1e3a8a; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; display: inline-block;">
                    Valider le rapport
                </a>
            </p>
        @else
            <p>Le technicien a terminé l'intervention et a joint le PV scanné.</p>
            @if($report->pv_file)
                <p style="margin-top: 20px;">
                    <a href="{{ Storage::url($report->pv_file) }}" 
                       style="background: #10b981; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; display: inline-block;">
                        Télécharger le PV
                    </a>
                </p>
            @endif
        @endif

        <p style="margin-top: 40px; font-size: 13px; color: #64748b;">
            Cordialement,<br>
            <strong>L’équipe Geir</strong>
        </p>
    </div>
</body>
</html>