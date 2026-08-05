<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; size: A4 landscape; }
        html, body {
            margin: 0;
            font-family: 'DejaVu Serif', serif;
            color: #232A33;
            background-color: #F6F7F9;
        }
        .frame {
            margin: 160px 60px 0;
            text-align: center;
        }
        .logo {
            width: 72px;
            height: 72px;
        }
        .org {
            font-size: 12px;
            letter-spacing: 2px;
            color: #16305C;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 12px;
        }
        .title {
            font-size: 32px;
            color: #0B1D3A;
            font-weight: bold;
            margin-top: 18px;
        }
        .sub {
            font-size: 12px;
            color: #232A33;
            margin-top: 24px;
        }
        .name {
            font-size: 26px;
            color: #16305C;
            font-weight: bold;
            margin-top: 10px;
            border-bottom: 1px solid #B08D3D;
            display: inline-block;
            padding-bottom: 6px;
        }
        .body-text {
            font-size: 12px;
            color: #232A33;
            margin-top: 24px;
            line-height: 1.6;
        }
        .footer {
            margin-top: 50px;
            font-size: 10px;
            font-family: 'DejaVu Sans Mono', monospace;
            color: #232A33;
        }
    </style>
</head>
<body>
    <div class="frame">
        <img class="logo" src="{{ public_path('assets/images/logo.png') }}">
        <div class="org">North Borneo Banking Executive Union</div>
        <div class="title">Certificate of Membership</div>

        <div class="sub">This is to certify that</div>
        <div class="name">{{ $user->name }}</div>

        <div class="body-text">
            is officially registered as a member {{ $user->company ? 'from '.$user->company : '' }}<br>
            of North Borneo Banking Executive Union with Member No. <strong>{{ $user->member_no }}</strong>
        </div>

        <div class="footer">
            Certificate No.: {{ $certNumber }} &nbsp;&bull;&nbsp; Issued: {{ now()->format('d M Y') }}
        </div>
    </div>
</body>
</html>
