<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; }
        .card { position: relative; width: 253px; height: 295px; overflow: hidden; }
        .card-bg { position: absolute; top: 0; left: 0; width: 253px; height: 295px; }
        .field {
            position: absolute;
            left: 0; width: 253px;
            text-align: center;
            color: #3B2500;
            white-space: nowrap;
            overflow: hidden;
        }
        .f-name { top: 204px; font-size: 9px; font-weight: bold; letter-spacing: 0.5px; }
        .f-id   { top: 225px; font-size: 8px; }
        .f-web  { top: 244px; font-size: 7px; color: #5C4000; }
    </style>
</head>
<body>
    <div class="card">
        <img class="card-bg" src="{{ public_path('assets/illustrations/front-kad-ahli-new.png') }}">
        {{-- dompdf clips hard — truncate server-side --}}
        <div class="field f-name">{{ Str::upper(Str::limit($user->name, 30, '')) }}</div>
        <div class="field f-id">Member ID {{ $memberCard->card_number }}</div>
        <div class="field f-web">www.nbbeu.org.my</div>
    </div>
</body>
</html>
