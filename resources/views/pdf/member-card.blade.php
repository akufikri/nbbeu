<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; }
        .card { position: relative; width: 340px; height: 214px; overflow: hidden; page-break-after: always; }
        .card:last-child { page-break-after: avoid; }
        .card-bg { position: absolute; top: 0; left: 0; width: 340px; height: 214px; }

        .photo-box {
            position: absolute;
            left: 234.6px; top: 36px; width: 68.7px; height: 75.8px;
            overflow: hidden;
        }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }

        .field {
            position: absolute;
            left: 55.8px; width: 108.8px;
            font-size: 8.5px;
            color: #16305C;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .field-name { top: 40.6px; }
        .field-phone { top: 67.5px; }
        .field-email { top: 95.2px; }
        .field-location { top: 123.8px; }

        .qr-box {
            position: absolute;
            left: 246.5px; top: 116.6px; width: 40.8px; height: 40.8px;
        }
        .qr-box img { width: 100%; height: 100%; }
    </style>
</head>
<body>
    <div class="card">
        <img class="card-bg" src="{{ public_path('assets/illustrations/front-kad-ahli.png') }}">

        <div class="photo-box">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}">
            @endif
        </div>

        <div class="field field-name">{{ $user->name }}</div>
        <div class="field field-phone">{{ $user->phone }}</div>
        <div class="field field-email">{{ $user->email }}</div>
        <div class="field field-location">{{ $location }}</div>

        <div class="qr-box">
            <img src="{{ $qrDataUri }}">
        </div>
    </div>

    <div class="card">
        <img class="card-bg" src="{{ public_path('assets/illustrations/back-kad-ahli.png') }}">
    </div>
</body>
</html>
