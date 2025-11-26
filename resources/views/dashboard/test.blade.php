<!DOCTYPE html>
<html>
<body>

<form action="https://dev.dorraprint.com/api/v1/user/fawry/payment/callback" method="POST">
    <input type="text" name="merchantRefNumber" value="12345">
    <input type="text" name="fawryRefNumber" value="67890">
    <input type="text" name="orderStatus" value="PAID">
    <input type="text" name="paymentMethod" value="CARD">

    <button type="submit">Send Test</button>
</form>

</body>
</html>
