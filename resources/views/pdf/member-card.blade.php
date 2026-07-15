<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            margin: 0;
            font-family: 'DejaVu Sans', sans-serif;
            width: 340px;
            height: 214px;
        }
        .card {
            width: 340px;
            height: 214px;
            background-color: #16305C;
            background-image: linear-gradient(135deg, #16305C 0%, #0B1D3A 100%);
            color: #F6F7F9;
            padding: 18px 20px;
            box-sizing: border-box;
        }
        .org {
            font-size: 9px;
            letter-spacing: 1px;
            color: #B08D3D;
            text-transform: uppercase;
            font-weight: bold;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 2px;
        }
        .name {
            font-size: 16px;
            font-weight: bold;
            margin-top: 22px;
        }
        .company {
            font-size: 10px;
            color: #C9D2E0;
            margin-top: 2px;
        }
        .meta {
            margin-top: 16px;
            font-size: 9px;
            color: #C9D2E0;
        }
        .meta strong {
            color: #F6F7F9;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        .qr {
            position: absolute;
            top: 18px;
            right: 20px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="org">North Borneo Banking Executive Union</div>
        <div class="title">Member Card</div>

        <img class="qr" src="{{ $qrDataUri }}" width="60" height="60">

        <div class="name">{{ $user->name }}</div>
        <div class="company">{{ $user->company }}</div>

        <div class="meta">
            Member No.: <strong>{{ $user->member_no }}</strong><br>
            Valid Until: <strong>{{ $memberCard->expires_at->format('d M Y') }}</strong>
        </div>
    </div>
</body>
</html>
