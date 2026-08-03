<?php
$headerImgPlace 		= HTTP_PATH.'/img/front/certificates/participation_certificate/participation_certificate_header_new.png';
$footerImgPlace 		= HTTP_PATH.'/img/front/certificates/participation_certificate/participation_certificate_footer_new.png';
$signatureImgPlace 	= HTTP_PATH.'/img/front/certificates/participation_certificate/participation_certificate_signature.png';
?>

<?php
if(count($placeCertData)>0)
{

foreach($placeCertData as $placecertIndex => $placecert)
{
?>
	<div style="padding: 0px; margin:0px;font-family:Arial,sans-serif,'Open Sans';font-weight:500;width: 100%;page-break-inside: avoid;break-inside: avoid-page;">
		<div class="container" style="max-width:100%;margin:0px auto;background-color: #fff; padding: 0px; border:0px;">
			<div class="maincontainer" style="max-width: 100%;background: #fff;margin:0px auto;border:0px;box-sizing:border-box;">
				<div class="header" style="width: 100%;display: block;padding: 0px 0px 0px;line-height:0;font-size:0;">
					<img src="<?php echo $headerImgPlace; ?>" style="width: 100%; display:block;">
				</div>
				<div class="contentpart" style="width: 100%; text-align:center;">
					<p class="simpletextt" style="font-family: arial;font-size: 14px;    margin-top: -30px; padding: 0px;">
						Accelerate Educational Ministries 
					</p>
					<p class="simpletextt" style="font-family: arial;font-size: 14px;    margin-top: 0px; padding: 0px; margin-bottom: 0px;">
						in affiliation with 
					</p>
					<div><i style="font-size: 30px;
						padding: 5px 0px 5px;
						display: block; font-weight: 500; font-family: arial;">Accelerate Christian Education <span>®</span></i></div>
					<span style="font-size: 14px; display: block; width: 100%;">takes pleasure in presenting this Award to</span>
					<div><i style="    font-size: 24px;
						padding: 5px 0px 5px;
						display: block; font-family: Pinyon Script, cursive;"><?php echo $arrCertData['student_name']; ?></i></div>
					<span style="font-size: 12px; width: 100%; display: block; padding-top: 5px;
						letter-spacing: 0.5px;"> of</span>
					<div style="margin-bottom: -20px;" >
						<h5 style="font-size: 20px;
							font-style: italic;
							letter-spacing: 0.8px; width: 100%; display: block;    margin: 10px 0px 10px;
							font-weight: 500; font-family: Pinyon Script, cursive;"><?php echo $arrCertData['school_name']; ?></h5>
					</div>
					<span style="font-size: 16px; width: 100%; display: block; padding-top: 10px;
						letter-spacing: 0.5px;"> For placing <b><?php echo $resultPositions[$placecert['position']]; ?></b> in <b><?php echo $placecert['event_name']; ?></b> at</span>
					<div style="margin-bottom: -110px;" >
						<h5 style="font-size: 30px;
							font-style: italic;
							letter-spacing: 0.8px; width: 100%;display: block;margin: 10px 0px 30px;
							font-weight: 500; font-family: Pinyon Script, cursive;"><?php echo $arrCertData['convention_name']; ?> <?php echo $arrCertData['season_year']; ?></h5>
					</div>
					<div style="    width: 110px;
						position: relative;
						top: 80px;
						left: 200px;">
						<img src="<?php echo $signatureImgPlace; ?>" style="width: 60px;">
						<b style="position: absolute;
							top: 43px;
							left: 10px;
							font-size: 10px;">Slabbert Pretorius</b>
						<p style="    position: absolute;
							top: 57px;
							left: 3px;
							font-size: 9px;
							padding: 0px;
							margin: 0px;">MANAGING DIRECTOR</p>
						<span style="position: absolute;
							top: 69px;
							left: -30px;
							font-size: 10px;
							width: 200px;">Southern Cross Educational Enterprises Ltd.</span>
					</div>
					<div style="    position: relative;
						width: 280px;
						left: 120px;
						font-size: 12px;
						font-weight: bold; font-style: italic;
						top: 120px;">
						And whatsoever you do, do it heartily, as to the<br>
						Lord, and not unto men" Colossians 3:23
					</div>
				</div>
				<div class="footer" style="width: 100%;display: block;padding: 0px;box-sizing: border-box;page-break-inside: avoid;break-inside: avoid-page;line-height:0;font-size:0;">
					<img src="<?php echo $footerImgPlace; ?>" style="width: 100%; display:block;">
				</div>
			</div>
		</div>
	</div>
<?php
	if($placecertIndex < (count($placeCertData) - 1))
	{
		echo '<div class="page-break"></div>';
	}
}
?>
<?php
}
?>