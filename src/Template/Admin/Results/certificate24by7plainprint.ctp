<?php
$arrCertData = $arrCertData ?? [];
$signatureImg = HTTP_PATH.'/img/front/certificates/signature.png';
$coordinatorSignatureImg = HTTP_PATH.'/img/front/Llewellyn Graham.png';
$studentName = trim((string)($arrCertData['student_name'] ?? ''));
$schoolName = trim((string)($arrCertData['school_name'] ?? ''));
$isApimeleki = $studentName === 'Apimeleki Tawake' && $schoolName === 'Christian Outreach College';
$isKyleStrydom = $studentName === 'Kyle Strydom' && $schoolName === 'Totara College of Accelerated Learning';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>24/7 Plain Print Certificate</title>
    <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            color: #000;
            background: #fff;
        }
        .sheet {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            box-sizing: border-box;
            padding: 0;
            border: none;
            overflow: hidden;
            position: relative;
        }
        .content-block {
            width: 100%;
            height: 100%;
            text-align: center;
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .script-name {
            margin-top: 0;
            font-family: "Pinyon Script", cursive;
            font-size: 40px;
            line-height: 1.1;
            height: 28px;
        }
        .script-school {
            margin-top: 0;
            font-family: "Pinyon Script", cursive;
            font-size: 24px;
            line-height: 1.1;
            padding-top: 36px;
        }
        .signature-wrap {
            padding: 74px 0 14px;
            text-align: center;
            position: relative;
            left: -220px;
            top: 96px;
            width: 100%;
            box-sizing: border-box;
        }
        .kyle-layout .script-name {
            margin-top: 120px;
        }
        .kyle-layout .script-school {
            padding-top: 56px;
        }
        .kyle-layout .signature-wrap {
            top: 170px;
        }
        .apimeleki-layout .script-name {
            margin-top: 280px;
        }
        .apimeleki-layout .script-school {
            padding-top: 36px;
        }
        .apimeleki-layout .signature-wrap {
            top: 20px;
        }
        .apimeleki-layout .coordinator-signoff {
            top: -100px;
        }
        .signature {
            width: 150px;
            max-width: 150px;
            height: auto;
        }
        .signature-name {
            margin-top: 2px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.1;
        }
        .signature-title,
        .signature-company {
            font-size: 10px;
            line-height: 1.1;
        }
        .coordinator-signoff {
            position: relative;
            left: 220px;
            top: -120px;
            width: 100%;
            text-align: center;
            box-sizing: border-box;
            padding: 14px 0 0;
        }
        .coordinator-signature {
            width: 150px;
            max-width: 150px;
            height: auto;
        }
        .coordinator-name {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.1;
        }
        .coordinator-title {
            font-size: 10px;
            font-weight: 700;
            line-height: 1.1;
        }
        .coordinator-company {
            font-size: 10px;
            line-height: 1.1;
        }
        .signature img {
            width: 150px;
            max-width: 150px;
            height: auto;
        }
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="content-block<?php echo $isKyleStrydom ? ' kyle-layout' : ''; ?><?php echo $isApimeleki ? ' apimeleki-layout' : ''; ?>">
            <div class="script-name"><?php echo h($studentName); ?></div>
            <div class="script-school"><?php echo h($schoolName); ?></div>

            <div class="signature-wrap">
                <img class="signature" src="<?php echo $signatureImg; ?>" alt="Signature">
                <div class="signature-name">Slabbert Pretorius</div>
                <div class="signature-title">MANAGING DIRECTOR</div>
                <div class="signature-company">Southern Cross Educational Enterprises Ltd.</div>
            </div>

            <div class="coordinator-signoff">
                <img class="coordinator-signature" src="<?php echo $coordinatorSignatureImg; ?>" alt="Signature">
                <div class="coordinator-name">Llewellyn Graham</div>
                <div class="coordinator-title">Events Coordinator</div>
                <div class="coordinator-company">Southern Cross Educational Enterprises Ltd.</div>
            </div>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
