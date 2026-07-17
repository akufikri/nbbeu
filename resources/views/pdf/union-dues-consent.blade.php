<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 40px; size: A4 portrait; }
        body {
            margin: 0;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #232A33;
        }
        .org {
            font-size: 13px;
            font-weight: bold;
            color: #16305C;
            text-transform: uppercase;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 24px;
            text-decoration: underline;
        }
        .meta { margin-top: 16px; }
        .body-text { margin-top: 20px; line-height: 1.7; text-align: justify; }
        .signature { margin-top: 60px; }
        .sig-line { margin-top: 40px; border-top: 1px solid #232A33; width: 220px; }
        .footer { margin-top: 50px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="org">North Borneo Banking Executive Union</div>
    <div class="title">Deduction of Trade Union Dues</div>

    <div class="meta">
        Date: {{ now()->format('d M Y') }}<br><br>
        The Manager,<br>
        {{ $profile->employer_name ?? '[Employer Name]' }}<br>
        {{ $profile->employer_address ?? '' }}
    </div>

    <div class="body-text">
        <p>Dear Sir/Madam,</p>

        <p>
            I, <strong>{{ $user->name }}</strong>, holder of IC No. <strong>{{ $profile->ic_no ?? '[IC No.]' }}</strong>,
            hereby request and authorise you to deduct the sum of <strong>RM {{ number_format($mandate->deduction_amount, 2) }}</strong>
            from my monthly salary, in favour of the North Borneo Banking Executive Union (NBBEU), as my monthly
            union dues, and to remit the said amount to NBBEU accordingly.
        </p>

        <p>
            This authorisation shall remain in effect until I notify you and NBBEU in writing to cancel it.
        </p>
    </div>

    <div class="signature">
        <p>Yours faithfully,</p>
        <div class="sig-line"></div>
        <p>({{ $user->name }})</p>
    </div>

    <div class="footer">
        NBBEU Secretariat, [Address on file] &nbsp;&bull;&nbsp; Generated: {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
