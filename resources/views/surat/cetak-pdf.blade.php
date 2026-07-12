{{-- ============================================================ --}}
{{-- resources/views/surat/cetak-pdf.blade.php                   --}}
{{-- Template PDF surat DomPDF                                   --}}
{{-- ============================================================ --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $surat->nomor_surat }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1B3C53;
            padding: 40px 50px;
        }

        /* Kop surat */
        .kop {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 12px;
            border-bottom: 3px solid #1B3C53;
            margin-bottom: 4px;
        }

        .kop-logo {
            width: 55px;
            height: 55px;
            border: 2px solid #1B3C53;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            color: #1B3C53;
            text-align: center;
            line-height: 55px;
            flex-shrink: 0;
        }

        .kop-info {
            flex: 1;
        }

        .kop-nama {
            font-size: 16px;
            font-weight: bold;
            color: #1B3C53;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .kop-alamat {
            font-size: 9px;
            color: #456882;
            margin-top: 2px;
        }

        .divider-thin {
            height: 1px;
            background: #E3E3E3;
            margin-bottom: 20px;
        }

        /* Nomor surat */
        .nomor-surat {
            text-align: center;
            margin-bottom: 20px;
            font-size: 10px;
            color: #456882;
        }

        /* Konten surat */
        .konten {
            line-height: 1.8;
            white-space: pre-wrap;
            font-size: 11px;
            color: #1B3C53;
            text-align: justify;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            border-top: 1px solid #E3E3E3;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            color: #9CA3AF;
        }
    </style>
</head>

<body>

    {{-- Kop --}}
    <div class="kop">
        <div class="kop-logo">PP</div>
        <div class="kop-info">
            <p class="kop-nama">{{ config('siak.pondok.nama') }}</p>
            <p class="kop-alamat">{{ config('siak.pondok.alamat', '') }}</p>
            <p class="kop-alamat">
                Telp: {{ config('siak.pondok.telp', '-') }} |
                Email: {{ config('siak.pondok.email', '-') }}
            </p>
        </div>
    </div>
    <div class="divider-thin"></div>

    {{-- Nomor surat --}}
    <div class="nomor-surat">
        Nomor: <strong>{{ $surat->nomor_surat }}</strong>
    </div>

    {{-- Konten --}}
    <div class="konten">{{ $kontenRendered }}</div>

    {{-- Footer --}}
    <div class="footer">
        <span>Dicetak: {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</span>
        <span>{{ config('siak.pondok.nama') }}</span>
        <span>{{ $surat->nomor_surat }}</span>
    </div>

</body>

</html>