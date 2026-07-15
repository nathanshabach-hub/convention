<?php
$themes = [
    'silver_apple' => [
        'header' => 'header_silver_apple_award.png',
        'footer' => 'footer_grey.png',
        'desc_prefix' => 'for memorising the book of',
        'verse' => "A word fitly spoken is like apples of gold<br>in pictures of silver<br>Proverbs 25:11",
    ],
    'golden_apple' => [
        'header' => 'header_golden_apple_award.png',
        'footer' => 'footer_yellow.png',
        'desc_prefix' => 'for memorising the book of',
        'verse' => "A word fitly spoken is like apples of gold<br>in pictures of silver<br>Proverbs 25:11",
    ],
    'golden_lamb' => [
        'header' => 'header_golden_lamp_award.png',
        'footer' => 'footer_yellow.png',
        'desc_prefix' => 'for',
        'verse' => "",
    ],
    'golden_harp' => [
        'header' => 'header_golden_harp_award.png',
        'footer' => 'footer_yellow.png',
        'desc_prefix' => 'for',
        'verse' => "",
    ],
    'christian_worker' => [
        'header' => 'header_christian_worker_award.png',
        'footer' => 'footer_yellow.png',
        'desc_prefix' => 'for',
        'verse' => "",
    ],
    'christian_soilder' => [
        'header' => 'header_christian_soldier_award.png',
        'footer' => 'footer_yellow.png',
        'desc_prefix' => 'for',
        'verse' => "",
    ],
];
$defaultTheme = [
    'header' => 'header_division_certificate_portrait.png',
    'footer' => 'footer_division_certificate_portrait.png',
    'desc_prefix' => 'for',
    'verse' => "",
];
$signatureImg = HTTP_PATH.'/img/front/certificates/signature.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Batch Certificates</title>
    <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; font-weight: 500; background: #fff; }
        .cert-page {
            page-break-after: always;
            page-break-inside: avoid;
            width: 100%;
        }
        .cert-page:last-child { page-break-after: avoid; }
        @page { size: A4 landscape; margin: 0cm; }
    </style>
</head>
<body>
<?php foreach ($certificates as $arrCertData):
    $theme = isset($themes[$arrCertData['cert_type']]) ? $themes[$arrCertData['cert_type']] : $defaultTheme;
    $headerImg = HTTP_PATH.'/img/front/certificates/'.$theme['header'];
    $footerImg = HTTP_PATH.'/img/front/certificates/'.$theme['footer'];
    $descPrefix = $theme['desc_prefix'];
    $verse = $theme['verse'];
?>
<div class="cert-page">
    <div style="max-width:100%;margin:0 auto;background:#fff;padding:0;">
        <div style="width:100%;display:block;padding:0;">
            <img src="<?php echo $headerImg; ?>" style="width:100%;display:block;">
        </div>
        <div style="width:100%;text-align:center;padding:0 1%;">
            <div style="position:relative;top:-100px;right:12%;font-size:14px;font-weight:bold;text-align:right;">
                <?php echo htmlspecialchars($arrCertData['convention_name']); ?>
            </div>
            <p style="font-family:arial;font-size:14px;margin-top:-20px;padding:0;">
                Accelerate Educational Ministries takes pleasure in presenting this award to
            </p>
            <div>
                <i style="font-size:30px;padding:10px 0 5px;display:block;font-weight:500;font-family:'Pinyon Script',cursive;">
                    <?php echo htmlspecialchars($arrCertData['name']); ?>
                </i>
            </div>
            <span style="font-size:14px;display:block;width:100%;">from</span>
            <div>
                <i style="font-size:24px;padding:10px 0 5px;display:block;font-family:'Pinyon Script',cursive;">
                    <?php echo htmlspecialchars($arrCertData['school_name']); ?>
                </i>
            </div>
            <?php if (!empty($arrCertData['description'])): ?>
            <span style="font-size:12px;width:100%;display:block;padding-top:10px;letter-spacing:0.5px;">
                <?php echo htmlspecialchars($descPrefix); ?>
            </span>
            <div style="margin-bottom:-110px;">
                <h5 style="font-size:30px;font-style:italic;letter-spacing:0.8px;width:100%;display:block;margin:10px 0 30px;font-weight:500;font-family:'Pinyon Script',cursive;">
                    <?php echo htmlspecialchars($arrCertData['description']); ?>
                </h5>
            </div>
            <?php endif; ?>
            <div style="width:110px;position:relative;top:120px;left:180px;">
                <img src="<?php echo $signatureImg; ?>" style="width:60px;">
                <b style="position:absolute;top:43px;left:10px;font-size:10px;">Slabbert Pretorius</b>
                <p style="position:absolute;top:57px;left:3px;font-size:9px;padding:0;margin:0;">MANAGING DIRECTOR</p>
                <span style="position:absolute;top:69px;left:-30px;font-size:10px;width:200px;">Southern Cross Educational Enterprises Ltd.</span>
            </div>
            <?php if ($verse): ?>
            <div style="position:relative;width:240px;left:39%;font-size:12px;font-weight:bold;font-style:italic;top:40px;">
                <?php echo $verse; ?>
            </div>
            <?php endif; ?>
        </div>
        <div style="width:100%;display:inline-block;padding:0;">
            <img src="<?php echo $footerImg; ?>" style="width:100%;display:block;">&nbsp;
        </div>
    </div>
</div>
<?php endforeach; ?>
<script>window.print();</script>
</body>
</html>
