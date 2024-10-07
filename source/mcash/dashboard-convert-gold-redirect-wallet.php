<html>
    <body>
        <form id="responseForm" action="<?php echo $_GET['location']; ?>" method="post">
            <input type="hidden" name="merchant" value="<?php echo $_GET['merchant']; ?>">
            <input type="hidden" name="action" value="<?php echo $_GET['action']; ?>">
            <input type="hidden" name="orderId" value="<?php echo $_GET['orderId']; ?>">
            <input type="hidden" name="refId" value="<?php echo $_GET['refId']; ?>">
            <input type="hidden" name="amount" value="<?php echo $_GET['amount']; ?>">
            <input type="hidden" name="description" value="<?php echo $_GET['description']; ?>">
            <input type="hidden" name="callbackURL" value="<?php echo $_GET['callbackURL']; ?>">
            <input type="hidden" name="redirectURL" value="<?php echo $_GET['redirectURL']; ?>">
            <input type="hidden" name="hash" value="<?php echo $_GET['hash']; ?>">
        </form>        
        <script>
			document.getElementById('responseForm').submit();
        </script>
    </body>
</html>