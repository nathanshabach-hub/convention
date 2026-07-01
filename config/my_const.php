<?php

define('DB_HOST','127.0.0.1');
define('DB_USER','convention_acp_testing');
define('DB_PASSWORD','*Z^jFAR(AOMh{Z50');
define('DB_NAME','convention_acp_demo_test');

define('SITE_TITLE','Accelerate');
define('ADMIN_TITLE', SITE_TITLE.' :: ');

define('TAG_LINE', "");
define('MAIL_FROM', 'info@scee.edu.au');
define('TITLE_FOR_PAGES', " :: ".SITE_TITLE);

// Keep production defaults, but allow localhost/env override for local runs.
$httpHost = isset($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : '';
$hostNameOnly = strtolower(preg_replace('/:\\d+$/', '', $httpHost));
$isLocalHost = in_array($hostNameOnly, ['localhost', '127.0.0.1', '::1'], true);
$httpScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

$envHttpPath = getenv('HTTP_PATH');
if ($envHttpPath === false || $envHttpPath === '') {
	$envHttpPath = ($isLocalHost && $httpHost !== '')
		? $httpScheme . '://' . $httpHost
		: 'https://convention.accelerateministries.com.au/acp_demo_test';
}

$envBasePath = getenv('BASE_PATH');
if ($envBasePath === false || $envBasePath === '') {
	$envBasePath = dirname(__DIR__);
}

define('HTTP_PATH', rtrim($envHttpPath, '/'));
define("BASE_PATH", $envBasePath);
define("HTTP_IMAGE",HTTP_PATH . '/img');

/*
define("SMTP_HOST",'smtp.gmail.com');
define("SMTP_USERNAME",'acp@scee.edu.au');
define("SMTP_PASSWORD",'fast1405[ACP]');
*/

define("SMTP_HOST",'convention.accelerateministries.com.au');
define("SMTP_USERNAME",'smtp@convention.accelerateministries.com.au');
define("SMTP_PASSWORD",'WWZ%s7#giA!4');

define("HEADERS_FROM_NAME",'Accelerate Educational Ministries');
define("HEADERS_FROM_EMAIL",'info@scee.edu.au');
define("HEADERS_CC",'events@scee.edu.au');
define("ACCOUNTS_TEAM_ANOTHER_EMAIL",'events@scee.edu.au');

// google captcha keys
define('SITEKEY', '6Lf5Kq0UAAAAAARKm3pZhnHF_rKTPNK6Xf3mxm7V');
define('SECRETKEY', '6Lf5Kq0UAAAAANRncOBz4BK4aWCktPPw2vWaCvAF');

define('CURR', 'AUD');

global $yesNoDD;
$yesNoDD = array(
    '1' => 'Yes',
	'0' => 'No',
);

global $genderDD;
$genderDD = array(
    'Male' 					=> 'Male',
	'Female' 				=> 'Female'
);

$startY = date("Y")-20;
$endY 	= date("Y")-8;

global $birthYearDD;
for($iCntr=$startY;$iCntr<=$endY;$iCntr++)
{
	$birthYearDD[$iCntr] = $iCntr;
}

global $conventionTypeDD;
$conventionTypeDD = array(
    '0' 	=> 'In Person Convention (0)',
	'1' 	=> 'Online Convention (1)',
	'3' 	=> 'Small Convention (3)'
);

global $eventTypeDD;
$eventTypeDD = array(
    '0' 	=> 'In Person Only Events (0)',
	'1' 	=> 'Variable Upload Events (1)',
	'2' 	=> 'Always Upload Events (2)'
);

global $eventUploadTypeDD;
$eventUploadTypeDD = array(
    'Nil' 				=> 'Nil',
	'Document '			=> 'Document',
	'Image' 			=> 'Image',
	'Video' 			=> 'Video',
	'Video/Audio' 		=> 'Video/Audio'
);

global $eventGroupNameDD;
$eventGroupNameDD = array(
	'1' 	=> 'U14',
	'2' 	=> 'U16',
	'3' 	=> 'U17',
	'4' 	=> 'Open'
);

global $eventGenderDD;
$eventGenderDD = array(
	'F' 	=> 'F',
	'M' 	=> 'M'
);

global $loginUserTypes;
$loginUserTypes = array(
    'School' => 'School',
	'Teacher_Parent' => 'Supervisor',
	'Student' => 'Student',
	'Judge' => 'Judge',
);

define('PAYPAL_MODE', 'Live'); // options 1. Sandbox  2. Live

// changed here 12-feb-2024
global $priceStructureCR;
$priceStructureCR = array(
    'full_registration' => 'Full Registration',
	'scripture_only_registration' => 'Scripture only registration',
	'student_registration_fees' => 'Student registration',
	'non_competitor_registration_fees' => 'Non-competitor registration',
	'non_affiliate_registration_fees' => 'Non-affiliate registration',
);

global $paymentStatus;
$paymentStatus = array(
    '0' => 'Failed',
	'1' => 'Confirmed',
	'2' => 'Pending',
	'3' => 'Invoiced',
);

global $romanNumbers;
$romanNumbers = array(
    '1' => 'I',
	'2' => 'II',
	'3' => 'III',
	'4' => 'IV',
	'5' => 'V',
	'6' => 'VI',
	'7' => 'VII',
	'8' => 'VIII',
	'9' => 'IX',
	'10' => 'X',
);

global $resultPoints;
$resultPoints = array(
    1 => 12,
    2 => 10,
    3 => 8,
    4 => 6,
	5 => 4,
	6 => 2,
);

define('UPLOAD_SCHOOLS_CSV_PATH', BASE_PATH . '/webroot/files/csv_files/');
define('DISPLAY_SCHOOLS_CSV_PATH', HTTP_PATH . '/files/csv_files/');

define('UPLOAD_EVENTS_HEART_PATH', BASE_PATH . '/webroot/files/events_heart/');
define('DISPLAY_EVENTS_HEART_PATH', HTTP_PATH . '/files/events_heart/');

define('UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH', BASE_PATH . '/webroot/files/events_submissions/');
define('DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH', HTTP_PATH . '/files/events_submissions/');

define('UPLOAD_JUDGING_REFERENCE_PDF_PATH', BASE_PATH . '/webroot/files/judgingformpdf/');
define('DISPLAY_JUDGING_REFERENCE_PDF_PATH', HTTP_PATH . '/files/judgingformpdf/');

global $eventKindID;
$eventKindID = array(
    'Elimination' => 'Elimination',
	'Sequential' => 'Sequential',
	'Judged' => 'Judged',
	'Pre-Judged' => 'Pre-Judged',
	'Group/Heats' => 'Group/Heats',
);

global $resultPositions;
$resultPositions = array(
    1 => '1<sup>st</sup>',
    2 => '2<sup>nd</sup>',
    3 => '3<sup>rd</sup>',
    4 => '4<sup>th</sup>',
	5 => '5<sup>th</sup>',
	6 => '6<sup>th</sup>',
);

global $weekDays;
$weekDays = array(
    'Monday' => 'Monday',
    'Tuesday' => 'Tuesday',
    'Wednesday' => 'Wednesday',
    'Thursday' => 'Thursday',
    'Friday' => 'Friday',
    'Saturday' => 'Saturday',
    'Sunday' => 'Sunday',
);

global $eventJudgeType;
$eventJudgeType = array(
    'general' => 'General',
	'times' => 'Times',
	'distances' => 'Distances',
	'scores' => 'Scores',
);

?>