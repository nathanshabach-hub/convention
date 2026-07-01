<?php
/** @var string $paypalURL */
/** @var object $settingsInfo */
/** @var string $itemName */
/** @var string $itemNumber */
/** @var string $totalAmount */
/** @var string $transaction_slug */
?>
<form action="<?= h($paypalURL) ?>" method="post" target="_top" id="paymentform">
    <input type="hidden" name="business" value="<?= h($settingsInfo->paypal_email) ?>">
    <input type="hidden" name="item_name" value="<?= h($itemName) ?>">
    <input type="hidden" name="item_number" value="<?= h($itemNumber) ?>">
    <input type="hidden" name="amount" value="<?= h($totalAmount) ?>">
    <input type="hidden" name="no_shipping" value="1">
    <input type="hidden" name="currency_code" value="AUD">
    <input type="hidden" name="notify_url" value="<?= h(HTTP_PATH . '/transactions/inpnotify/' . $transaction_slug) ?>">
    <input type="hidden" name="cancel_return" value="<?= h(HTTP_PATH . '/transactions/cancelbooking/' . $transaction_slug) ?>">
    <input type="hidden" name="return" value="<?= h(HTTP_PATH . '/transactions/paymentsuccess/' . $transaction_slug) ?>">
    <input type="hidden" name="rm" value="2">
    <input type="hidden" name="image_url" value="https://convention.accelerateministries.com.au/acp/img/front/main-logo-120px.png">
    <input type="hidden" name="display" value="1">
    <input type="hidden" name="cmd" value="_xclick">
    <img style="position: fixed; top: 50%; left: 50%;" src="<?= h(HTTP_PATH . '/img/loader_large_blue.gif') ?>">
</form>

<script>
document.getElementById('paymentform').submit();
</script>