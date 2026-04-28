<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .header { background: #1a56db; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; background: #f9fafb; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #1a56db; color: white; padding: 12px; text-align: left; }
        td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f3f4f6; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .status-hs { background: #fee2e2; color: #dc2626; }
        .total { font-size: 20px; font-weight: 700; color: #1a56db; text-align: right; margin-top: 10px; }
        .footer { padding: 16px 20px; background: #e5e7eb; text-align: center; font-size: 12px; color: #6b7280; border-radius: 0 0 8px 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0;">Devis de réparation</h1>
        <p style="margin:4px 0 0;">Intervention #{{ $intervention->id }}</p>
    </div>

    <div class="content">
        <p>Bonjour,</p>
        <p>Suite à notre intervention, voici la liste des éléments non fonctionnels constatés avec les prix de remplacement/réparation proposés :</p>

        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>État</th>
                    <th style="text-align:right;">Prix (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $labels = [
                        'signalisation_bouton_appel' => "Signalisation bouton d'appel",
                        'detection_metalique' => 'Détection métallique',
                        'message_vocale' => 'Message vocale',
                        'blocage_sas_porte_interieur' => 'Blocage SAS porte intérieur',
                        'issue_de_secours' => 'Issue de secours',
                        'barre_anti_panique' => 'Barre anti-panique',
                        'serrure_de_verrouillage' => 'Serrure de verrouillage',
                        'automatique' => 'Automatique',
                        'manuel' => 'Manuel',
                        'urgence' => 'Urgence',
                        'mode_femme_de_menage' => 'Mode femme de ménage',
                        'onduleur' => 'Onduleur',
                        'ferme_porte' => 'Ferme porte',
                        'serrure_mecanique_portes' => 'Serrure mécanique portes',
                        'paumelles_position_portes' => 'Paumelles et position portes',
                        'portes_sas' => 'Portes SAS',
                        'vitrage_lateraux_sas' => 'Vitrage latéraux SAS',
                        'vitrage_des_impostes' => 'Vitrage des impostes',
                    ];
                @endphp
                @foreach($defectiveItems as $key => $item)
                <tr>
                    <td>{{ $labels[$key] ?? $key }}</td>
                    <td><span class="status-badge status-hs">Non fonctionnel</span></td>
                    <td style="text-align:right; font-weight:600;">{{ number_format($item['price'], 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            Total : {{ number_format($totalPrice, 0, ',', ' ') }} FCFA
        </div>

        <p style="margin-top:20px;">N'hésitez pas à nous contacter pour toute question ou pour confirmer les réparations souhaitées.</p>
        <p>Cordialement,<br><strong>L'équipe GEER Maintenance</strong></p>
    </div>

    <div class="footer">
        Ce mail a été généré automatiquement par le système GEER Maintenance.
    </div>
</body>
</html>
