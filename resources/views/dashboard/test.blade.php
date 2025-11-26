<!DOCTYPE html>
<html>
<body>

<form action="https://dev.dorraprint.com/api/v1/user/fawry/payment/callback" method="POST">
    <input type="text" name="merchantRefNumber" value="ORD-20251126-000253">
    <input type="text" name="fawryRefNumber" value="780088768">
    <input type="text" name="orderStatus" value="Paid">
    <input type="text" name="paymentMethod" value="MWALLET">
    <input type="text" name="messageSignature" value="bfe8683620ba18bb0e72d04970df9fdcd2936d5712fe540502e93924a8cc5c25">
    <input type="text" name="paymentAmount" value="1435.6">
    <input type="text" name="orderAmount" value="1435.6">

    <button type="submit">Send Test</button>
</form>

</body>
</html>
