<?php
	require_once("controllers/callwallet.php");
?>
<html>
    <body>
        <form id="responseForm" action="<?php echo $requestData['location']; ?>" method="post">
            <input type="hidden" name="merchant" value="<?php echo $requestData['merchant']; ?>">
            <input type="hidden" name="action" value="<?php echo $requestData['action']; ?>">
            <input type="hidden" name="orderId" value="<?php echo $requestData['orderId']; ?>">
            <input type="hidden" name="refId" value="<?php echo $requestData['refId']; ?>">
            <input type="hidden" name="amount" value="<?php echo $requestData['amount']; ?>">
            <input type="hidden" name="description" value="<?php echo $requestData['description']; ?>">
            <input type="hidden" name="callbackURL" value="<?php echo $requestData['callbackURL']; ?>">
            <input type="hidden" name="redirectURL" value="<?php echo $requestData['redirectURL']; ?>">
            <input type="hidden" name="hash" value="<?php echo $requestData['hash']; ?>">
        </form>        
        <script>
			document.getElementById('responseForm').submit();
        </script>
    </body>
</html>