<?php
$arrCertData = $arrCertData ?? [];
$signatureImg = HTTP_PATH.'/img/front/certificates/signature.png';
$coordinatorSignatureImg = HTTP_PATH.'/img/front/Llewellyn Graham.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Division Winner Plain Print</title>
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
            max-width: 1000px;
            margin: 0 auto;
            min-height: 100vh;
            box-sizing: border-box;
            padding: 30px 40px;
            text-align: center;
        }
        .content-block {
            margin-top: 430px;
        }
        .script-name {
            margin-top: 0;
            font-family: "Pinyon Script", cursive;
            font-size: 34px;
            line-height: 1.1;
        }
        .script-school {
            margin-top: 14px;
            font-family: "Pinyon Script", cursive;
            font-size: 25px;
            line-height: 1.1;
        }
        .script-division {
            margin-top: 70px;
            font-family: "Pinyon Script", cursive;
            font-size: 40px;
            line-height: 1.1;
        }
        .signature-wrap {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0 40px;
        }
        .sig-block {
            text-align: center;
            width: 45%;
        }
        .sig-block img {
            width: 150px;
            max-width: 150px;
            height: auto;
        }
        .sig-name {
            margin-top: 4px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
        }
        .sig-title {
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
        }
        .sig-company {
            font-size: 10px;
            line-height: 1.2;
        }
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="content-block">
            <div class="script-name"><?php echo h($arrCertData['student_name'] ?? ''); ?></div>
            <div class="script-school"><?php echo h($arrCertData['school_name'] ?? ''); ?></div>
            <div class="script-division"><?php echo h($arrCertData['division_name'] ?? ''); ?></div>
            <div class="signature-wrap">
                <div class="sig-block">
                    <img src="<?php echo $signatureImg; ?>" alt="Signature">
                    <div class="sig-name">Slabbert Pretorius</div>
                    <div class="sig-title">MANAGING DIRECTOR</div>
                    <div class="sig-company">Southern Cross Educational Enterprises Ltd.</div>
                </div>
                <div class="sig-block" style="margin-top: 60px;">
                    <img src="<?php echo $coordinatorSignatureImg; ?>" alt="Signature">
                    <div class="sig-name">Llewellyn Graham</div>
                    <div class="sig-title">Events Coordinator</div>
                    <div class="sig-company">Southern Cross Educational Enterprises Ltd.</div>
                </div>
            </div>
        </div>

    </div>

    <script>
        window.print();
    </script>
</body>
</html>
