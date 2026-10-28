@php
    $nameY = $settings['name_field']['y'] ?? '45%';
    $roleY = $settings['role_field']['y'] ?? '68%';

    // Apply a fine -1.2% offset so DomPDF baseline matches HTML preview
    if (str_ends_with($nameY, '%')) {
        $nameY = (floatval($nameY) - 1.2) . '%';
    }
    if (str_ends_with($roleY, '%')) {
        $roleY = (floatval($roleY) - 1.2) . '%';
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado - {{ $certificate->recipient_name }}</title>
    <style>
        /* Custom fonts styling */
        {!! $fontStyles !!}

        @page {
            size: A4 landscape;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-image: url('{{ $bgPath }}');
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            box-sizing: border-box;
        }

        .name-field {
            position: absolute;
            left: {{ $settings['name_field']['x'] ?? '0%' }};
            top: {{ $nameY }};
            width: 100%;
            text-align: center;
            font-family: "{{ $settings['name_field']['font_family'] ?? 'Helvetica' }}", sans-serif;
            font-size: {{ $settings['name_field']['font_size'] ?? '32pt' }};
            color: {{ $settings['name_field']['color'] ?? '#111827' }};
            font-weight: bold;
            line-height: 1;
            margin: 0;
            padding: 0;
        }

        .role-field {
            position: absolute;
            left: {{ $settings['role_field']['x'] ?? '0%' }};
            top: {{ $roleY }};
            width: 100%;
            text-align: center;
            font-family: "{{ $settings['role_field']['font_family'] ?? 'Helvetica' }}", sans-serif;
            font-size: {{ $settings['role_field']['font_size'] ?? '16pt' }};
            color: {{ $settings['role_field']['color'] ?? '#4B5563' }};
            line-height: 1;
            margin: 0;
            padding: 0;
        }

        .verification {
            position: absolute;
            right: 12mm;
            bottom: 10mm;
            width: 28mm;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 7pt;
            color: #374151;
        }

        .verification svg {
            display: block;
            width: 25mm;
            height: 25mm;
            margin: 0 auto 2mm;
        }
    </style>
</head>
<body>

    <!-- Recipient Name -->
    <div class="name-field">
        {{ $certificate->recipient_name }}
    </div>

    <!-- Role (e.g. Participante, Ponente) -->
    <div class="role-field">
        En calidad de <strong>{{ $certificate->role ?? 'Participante' }}</strong>
    </div>

    <div class="verification">
        {!! $qrSvg !!}
        Verificación oficial
    </div>

</body>
</html>
