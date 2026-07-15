<?php
$themes = [
    'silver_apple' => [
        'header' => 'header_silver_apple_award.png',
        'footer' => 'footer_grey.png',
    ],
    'golden_apple' => [
        'header' => 'header_golden_apple_award.png',
        'footer' => 'footer_yellow.png',
    ],
    // Existing asset is named "lamp", but this maps to the user-facing "Golden Lamb" option.
    'golden_lamb' => [
        'header' => 'header_golden_lamp_award.png',
        'footer' => 'footer_yellow.png',
    ],
    'golden_harp' => [
        'header' => 'header_golden_harp_award.png',
        'footer' => 'footer_yellow.png',
    ],
    'christian_worker' => [
        'header' => 'header_christian_worker_award.png',
        'footer' => 'footer_yellow.png',
    ],
    'christian_soilder' => [
        'header' => 'header_christian_soldier_award.png',
        'footer' => 'footer_yellow.png',
    ],
];

$theme = isset($themes[$arrCertData['cert_type']]) ? $themes[$arrCertData['cert_type']] : [
    'header' => 'header_division_certificate_portrait.png',
    'footer' => 'footer_division_certificate_portrait.png',
];

$headerImg    = HTTP_PATH.'/img/front/certificates/'.$theme['header'];
$footerImg    = HTTP_PATH.'/img/front/certificates/'.$theme['footer'];
$signatureImg = HTTP_PATH.'/img/front/certificates/signature.png';
?>

<script type="text/javascript">
<!--
window.print();
//-->
</script>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $arrCertData['cert_type_label']; ?> :: <?php echo $arrCertData['convention_name']; ?> - <?php echo $arrCertData['season_year']; ?> For <?php echo $arrCertData['name']; ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
    </head>
    <body style="padding:0px;margin:0px;font-family:Arial,sans-serif,'Open Sans';font-weight:500;width:100%;border:0px solid #06402B;">
        <div style="max-width:100%;margin:0px auto;background-color:#fff;padding:0px;">
            <div style="max-width:100%;background:#fff;margin:0px auto;">
                <div style="width:100%;display:block;padding:0px;">
                    <img src="<?php echo $headerImg; ?>" style="width:100%;display:block;">
                </div>
                <div style="width:100%;text-align:center;padding:0px;">
                    <p style="font-family:arial;font-size:13px;margin-top:-30px;padding:0px;">
                        Accelerate Educational Ministries
                    </p>
                    <p style="font-family:arial;font-size:13px;margin-top:0px;padding:0px;margin-bottom:0px;">
                        in affiliation with
                    </p>
                    <div><i style="font-size:26px;padding:3px 0px;display:block;font-weight:500;font-family:arial;">Accelerate Christian Education <span>&reg;</span></i></div>

                    <span style="font-size:13px;display:block;width:100%;margin-top:4px;">
                        takes pleasure in presenting this <strong><?php echo htmlspecialchars($arrCertData['cert_type_label']); ?></strong> to
                    </span>

                    <div>
                        <i style="font-size:28px;padding:4px 0px 2px;display:block;font-family:'Pinyon Script',cursive;">
                            <?php echo htmlspecialchars($arrCertData['name']); ?>
                        </i>
                    </div>

                    <?php if (!empty($arrCertData['school_name'])): ?>
                    <span style="font-size:12px;width:100%;display:block;padding-top:3px;letter-spacing:0.5px;">
                        of
                    </span>
                    <div>
                        <h5 style="font-size:20px;font-style:italic;letter-spacing:0.8px;width:100%;display:block;margin:4px 0px;font-weight:500;font-family:'Pinyon Script',cursive;">
                            <?php echo htmlspecialchars($arrCertData['school_name']); ?>
                        </h5>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($arrCertData['recipient_type'])): ?>
                    <span style="font-size:11px;width:100%;display:block;padding-top:2px;letter-spacing:0.4px;">
                        Recipient Type: <?php echo ucfirst(htmlspecialchars($arrCertData['recipient_type'])); ?>
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($arrCertData['description'])): ?>
                    <span style="font-size:12px;width:100%;display:block;padding-top:3px;letter-spacing:0.5px;">
                        for
                    </span>
                    <div style="margin-bottom:-5px;">
                        <h5 style="font-size:20px;font-style:italic;letter-spacing:0.8px;width:100%;display:block;margin:4px 0px;font-weight:500;font-family:'Pinyon Script',cursive;">
                            <?php echo htmlspecialchars($arrCertData['description']); ?>
                        </h5>
                    </div>
                    <?php endif; ?>

                    <span style="font-size:12px;width:100%;display:block;padding-top:4px;letter-spacing:0.5px;">
                        at
                    </span>
                    <div style="margin-bottom:-100px;">
                        <h5 style="font-size:26px;font-style:italic;letter-spacing:0.8px;width:100%;display:block;margin:4px 0px 20px;font-weight:500;font-family:'Pinyon Script',cursive;">
                            <?php echo htmlspecialchars($arrCertData['convention_name']); ?> <?php echo htmlspecialchars($arrCertData['season_year']); ?>
                        </h5>
                    </div>

                    <div style="width:110px;position:relative;top:75px;left:200px;">
                        <img src="<?php echo $signatureImg; ?>" style="width:60px;">
                        <b style="position:absolute;top:43px;left:10px;font-size:10px;">Slabbert Pretorius</b>
                        <p style="position:absolute;top:57px;left:3px;font-size:9px;padding:0px;margin:0px;">MANAGING DIRECTOR</p>
                        <span style="position:absolute;top:69px;left:-30px;font-size:10px;width:200px;">Southern Cross Educational Enterprises Ltd.</span>
                    </div>

                    <div style="position:relative;width:280px;left:120px;font-size:12px;font-weight:bold;font-style:italic;top:120px;">
                        "And whatsoever you do, do it heartily, as to the<br>
                        Lord, and not unto men" Colossians 3:23
                    </div>
                </div>
                <div style="width:100%;display:inline-block;padding:0px;box-sizing:border-box;">
                    <img src="<?php echo $footerImg; ?>" style="width:100%;display:block;">&nbsp;
                </div>
            </div>
        </div>
    </body>
    <style>
        @page {
            size: A4 landscape;
            margin: 0cm;
        }
        * { box-sizing: border-box; }
    </style>
</html>
