<?php
// certificate theme
$headerImg 		= HTTP_PATH.'/img/front/certificates/header_24by7_certificate_portrait.png';
$footerImg 		= HTTP_PATH.'/img/front/certificates/footer_24by7_certificate_portrait.png';
$signatureImg 	= HTTP_PATH.'/img/front/certificates/signature.png';
$coordinatorSignatureImg = HTTP_PATH.'/img/front/Llewellyn Graham.png';
$borderColor 	= '#5d0b0d';
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
		<title>Certificate 24/7 - <?php echo $arrCertData['student_name']; ?></title>
		<!-- Bootstrap -->
		<link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
	</head>
	<body style="margin: 0; padding: 0; font-family: Arial, sans-serif, 'Open Sans'; font-weight: 500; box-sizing: border-box; background: #fff;">
		<div class="container" style="width: 210mm; height: 297mm; margin: 0 auto; background-color: #fff; padding: 0; overflow: hidden;">
			<div class="maincontainer" style="width: 210mm; height: 297mm; background: #fff; margin: 0 auto; position: relative; overflow: hidden; border: 1px solid <?php echo $borderColor; ?>; box-sizing: border-box;">
				<div class="header" style="width: 100%;display: block;padding: 0px 0px 0px;">
					<img src="<?php echo $headerImg; ?>" style="width: 100%;">
				</div>
				<div class="contentpart" style="width: 100%; text-align: center; position: relative; z-index: 2;">
					<p class="simpletextt" style="font-family: arial; font-size: 28px; margin-top: 14px; padding: 0px;">
						Accelerate Educational Ministries 
					</p>
					<i class="simpletextt" style="font-family: arial; font-size: 16px; margin-top: 0px; padding: 0px; margin-bottom: 0px;">
					in affiliation with </i>
					<div><i style="font-size: 34px; padding: 26px 0px 8px; display: block; font-weight: 500; font-family: arial;">Accelerate Christian Education <span style="font-size: 22px; position: relative; top: -10px; right: 5px;">®</span></i></div>
					<span style="padding-top: 26px; font-size: 16px; display: block; width: 100%; font-style: italic;">takes pleasure in presenting this Award to</span>
					
					<!--<div style="height: 425px;">ddddd</div>-->
					
					<div><i style="font-size: 40px; padding: 26px 0px 6px; display: block; font-family: Pinyon Script, cursive; height: 28px;"><?php echo $arrCertData['student_name']; ?></i></div>
					
					<div><i style="font-size: 24px; padding: 36px 0 0; display: block; font-family: Pinyon Script, cursive;"><?php echo $arrCertData['school_name']; ?></i></div>
					<div style="padding: 58px 0 18px; text-align: center; position: relative; left: -220px;">
						<img src="<?php echo $signatureImg; ?>" style="width: 150px; max-width: 150px; height: auto;">
						<div style="margin-top: 2px; font-size: 12px; font-weight: 700; line-height: 1.1;">Slabbert Pretorius</div>
						<div style="font-size: 10px; font-weight: 700; line-height: 1.1;">MANAGING DIRECTOR</div>
						<div style="font-size: 10px; line-height: 1.1;">Southern Cross Educational Enterprises Ltd.</div>
					</div>
					<div style="padding: 58px 0 18px; text-align: center; position: relative; left: 220px; top: -170px; width: 100%; box-sizing: border-box;">
						<img src="<?php echo $coordinatorSignatureImg; ?>" style="width: 150px; max-width: 150px; height: auto;">
						<div style="margin-top: 2px; font-size: 12px; font-weight: 700; line-height: 1.1;">Llewellyn Graham</div>
						<div style="font-size: 10px; font-weight: 700; line-height: 1.1;">Events Coordinator</div>
						<div style="font-size: 10px; line-height: 1.1;">Southern Cross Educational Enterprises Ltd.</div>
					</div>
					
					
					<div style="width: 100%; display: block; position: absolute; left: 0; bottom: 92px; transform: translateY(60px);">
						<div style="width:25%; float:left;font-style: italic;">
							<span>Commitment</span>
							<p style="margin: 0px;">jeremiah 24:7</p>
						</div>
						<div style="width:25%; float: left;font-style: italic;">
							<span>Worship</span>
							<p style="margin: 0px;">Psalm 24:7</p>
						</div>
						<div style="width:25%; float: left;font-style: italic;">
							<span>Obedience</span>
							<p style="margin:0px;">Exodus 24:7</p>
						</div>
						<div style="width:25%;float:left;font-style: italic;">
							<span>Wisdom</span>
							<p style="margin: 0px;">Proverbs 24:7</p>
						</div>
						<div style="clear: both;"></div>
						<div style="font-size: 20px; margin-top: 18px; font-style: italic;"><b>1</b> <span><b>L</b>ife</span> <b>24</b> hours a day <b>7</b> days a week <b>4</b> <span><b>C</b>hrist</span></div>
					</div>
				</div>
				<div class="footer" style="width: 100%; display: block; padding: 0; box-sizing: border-box; position: absolute; left: 0; bottom: 0;">
					<img src="<?php echo $footerImg; ?>" style="width: 100%; display: block;">
				</div>
			</div>
		</div>
	</body>
	<style>
		.pinyon-script-regular {
		font-family: "Pinyon Script", cursive;
		font-weight: 400;
		font-style: normal;
		}
		@page {
		size: A4 portrait;
		margin: 0;
		}
		@media print {
		body { margin: 0; padding: 0; }
		.container { transform: scale(0.96); transform-origin: 48% center; margin: 0 auto !important; }
		.maincontainer { outline: none !important; }
		}
	</style>
</html>
<script type="text/javascript">
<!--
window.print();
//-->
</script>