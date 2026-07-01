<?php
/**
 * Shared error layout for CakePHP 4 exception rendering.
 * Keep this in .php so MissingLayoutException does not occur
 * when the renderer looks for layout/error.php.
 */

$cakeDescription = SITE_TITLE;
?>
<!DOCTYPE html>
<html>
<head>
    <?php echo $this->Html->charset() ?>
    <title>
        <?php echo $cakeDescription ?>:
        <?php echo $this->fetch('title') ?>
    </title>
    <?php echo $this->Html->meta('icon') ?>

    <?php echo $this->Html->css('base.css') ?>
    <?php echo $this->Html->css('cake.css') ?>

    <?php echo $this->fetch('meta') ?>
    <?php echo $this->fetch('css') ?>
    <?php echo $this->fetch('script') ?>
    <?php echo $this->Html->css('front/style.css'); ?>
</head>
<body>
    <div id="container">
        <div id="content">
            <?php echo $this->fetch('content') ?>
        </div>
    </div>
</body>
</html>
