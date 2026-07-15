<?php
use Cake\ORM\TableRegistry;
$this->Conventionregistrationstudents = TableRegistry::get('Conventionregistrationstudents');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
$this->Books = TableRegistry::get('Books');

// certificate theme
$headerImg 		= HTTP_PATH.'/img/front/certificates/header_division_certificate_portrait.png';
$footerImg 		= HTTP_PATH.'/img/front/certificates/footer_division_certificate_portrait.png';
$signatureImg 	= HTTP_PATH.'/img/front/certificates/signature.png';
$borderColor 	= '#1a98d5';
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
		<title>Division Winner - <?php echo $arrCertData['student_name']; ?></title>
		<!-- Bootstrap -->
		<link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
	</head>
	<body style="margin: 0; padding: 0; font-family: Arial, sans-serif, 'Open Sans'; font-weight: 500; box-sizing: border-box; background: #fff;">
		<div class="container" style="width: 210mm; height: 297mm; margin: 0 auto; background-color: #fff; padding: 0; overflow: hidden;">
			<div class="maincontainer" style="width: 210mm; height: 297mm; background: #fff; margin: 0 auto; position: relative; overflow: hidden; border: 1px solid <?php echo $borderColor; ?>; box-sizing: border-box;">
				<div class="header" style="width: 100%; display: block; padding: 0;">
					<img src="<?php echo $headerImg; ?>" style="width: 100%;">
				</div>
				<div class="contentpart" style="width:100%; text-align:center; position: relative; z-index: 2; padding-top: 30px;">
					<p class="simpletextt" style="font-family: arial;font-size:25px;margin-top: 0px; padding: 0px;">
						Accelerate Educational Ministries 
					</p>
					<i class="simpletextt" style="font-family: arial;font-size: 14px;    margin-top: 0px; padding:0px; margin-bottom:0px;">
					in affiliation with </i>
					<div><span style="font-size:30px;padding:20px 0px 5px;display: block; font-weight: 500; font-family: arial; ">Accelerated Christian Education <span style="font-size: 20px;position: relative;top:-10px;right:5px;">®</span></span></div>
					<span style="padding-top:20px;font-size:14px;display:block;width:100%; font-style: italic;">takes pleasure in presenting this Divisional Certificate to</span>
					
					<div><i style="font-size:34px;padding:20px 0px 5px;display:block;font-family: Pinyon Script, cursive;"><?php echo $arrCertData['student_name']; ?></i></div>
					
					<div><i style="font-size:25px;padding:20px 0px 5px;display:block;font-family: Pinyon Script, cursive;"><?php echo $arrCertData['school_name']; ?></i></div>
					
					<div style="padding:20px 0px;font-style:italic;">For being the overall point-scorer in the </div>
					
					<div><i style="font-size:44px;padding:1px 0px 5px;display:block;font-family: Pinyon Script, cursive;"><?php echo $arrCertData['division_name']; ?></i></div>

					<div style="padding-top: 110px; text-align: center; position: relative; left: -220px;">
						<img src="<?php echo $signatureImg; ?>" style="width: 150px; max-width: 150px; height: auto;">
						<div style="margin-top: 2px; font-size: 12px; font-weight: 700; line-height: 1.1;">Slabbert Pretorius</div>
						<div style="font-size: 10px; font-weight: 700; line-height: 1.1;">MANAGING DIRECTOR</div>
						<div style="font-size: 10px; line-height: 1.1;">Southern Cross Educational Enterprises Ltd.</div>
					</div>
					<div style="padding-top: 24px; text-align: center; position: relative; left: 220px; top: -116px; width: 100%; box-sizing: border-box;">
						<img src="<?php echo HTTP_PATH.'/img/front/Llewellyn Graham.png'; ?>" style="width: 150px; max-width: 150px; height: auto;">
						<div style="margin-top: 2px; font-size: 12px; font-weight: 700; line-height: 1.1;">Llewellyn Graham</div>
						<div style="font-size: 10px; font-weight: 700; line-height: 1.1;">Events Coordinator</div>
						<div style="font-size: 10px; line-height: 1.1;">Southern Cross Educational Enterprises Ltd.</div>
					</div>
					
					<div style="width: 360px;
						display: block;
						font-size: 18px;
						position: absolute;
						left: 70px;
						bottom: -30px;
						font-style: italic;
						text-align: left;">
						<span>"And whatsover you do, do it <br> heartily, as to the lord, and not unto <br> men" Colossians 3:23</span>
					</div>
				</div>
				<div class="footer" style="width: 100%;display: block;padding: 0px;box-sizing: border-box; position: absolute; left: 0; bottom: 0;">
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