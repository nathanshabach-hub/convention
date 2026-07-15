<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $title; ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta name="theme-color" content="#1c2452">
  <link rel="manifest" href="/manifest.json">
  <link rel="apple-touch-icon" href="/img/pwa-icon-192.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="shortcut icon" type="image/x-icon" href="<?php echo HTTP_PATH; ?>/img/favicon.ico" /> 
  <?php echo $this->Html->css('admin/bootstrap.min.css'); ?>
  <?php echo $this->Html->css('admin/AdminLTE.min.css'); ?>
  <?php echo $this->Html->css('admin/all-skins.min.css'); ?>
  <?php echo $this->Html->css('admin/admin.css'); ?>
  <?php echo $this->Html->script('jquery-2.1.0.min.js'); ?>
  <?php echo $this->Html->script('jquery.validate.js'); ?>
  <?php echo $this->Html->script('app.min.js'); ?>
  <?php echo $this->Html->script('listing.js'); ?>
  
  <?php echo $this->Html->script('timepicker/mdtimepicker.js'); ?>
	<?php echo $this->Html->css('timepicker/mdtimepicker.css'); ?>
  
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
	<style type="text/css">
	.select2-container--default.select2-container--focus .select2-selection--multiple {
	border: solid #ccc 1px;
	outline: 0;
	}
	.select2-container--default .select2-selection--multiple {    
	border: 1px solid #d2d6de;
	border-radius: 0px;     
	}
	.select2-container--default.select2-container--focus .select2-selection--single {
	border: solid #ccc 1px;
	outline: 0;
	}
	.select2-container--default .select2-selection--single {    
	border: 1px solid #d2d6de;
	border-radius: 0px;
	height: 34px;
	padding: 6px 12px;
	}
	.select2-container .select2-selection--single .select2-selection__rendered {
	padding-left: 0px;
	}
	.select2-container--default .select2-selection--multiple .select2-selection__choice {

    color: #000;
}
	</style>

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php echo $this->element('Admin/header'); ?>
    <?php echo $this->element('Admin/left_menu'); ?>
    <?php echo $this->fetch('content'); ?>
</div>
    <script type="text/javascript">
            window.onload = function() {
                setTimeout("hideSessionMessage()",30000);
            };
            function hideSessionMessage(){
                $('.ersu_message').fadeOut("slow");
            }
        </script>

<!-- PWA: offline bar for admin -->
<div id="acp-offline-bar" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:9999;background:#1c2452;color:#fff;padding:10px 18px;font-size:14px;font-weight:600;align-items:center;justify-content:space-between;gap:12px;">
  <span>📡 You are offline — changes may not save until reconnected.</span>
  <span id="acp-sync-badge" style="display:none;background:#d6b23c;color:#1c2452;border-radius:999px;padding:3px 12px;font-size:12px;font-weight:700;"></span>
</div>
<script src="/js/acp-pwa.js"></script>
</body>
</html>
