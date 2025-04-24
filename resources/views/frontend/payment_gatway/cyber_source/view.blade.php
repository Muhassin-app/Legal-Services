<?php 
$endpoint_url = 'https://secureacceptance.cybersource.com/pay';

?>

@include('frontend/payment_gatway/cyber_source/config');
 @include('frontend/payment_gatway/cyber_source/security');

<!DOCTYPE html>
<html>
<head>
    <title>Confirm</title>
    <link rel="stylesheet" type="text/css" href="../css/payment.css"/>
</head>
<body>
<img src="../img/logo-cybersource.png" style="padding-bottom: 10px;" />
<h2>Review &amp; Confirm</h2>
<form id="payment_confirmation" action="<?php echo $endpoint_url ?>" method="post"/>

<fieldset id="confirmation">
    <legend>Payment Details</legend>
    <div>
        <?php
            foreach($params as $name => $value) {
                echo "<div>";
                echo "<span class=\"fieldName\">" . $name . "</span><span class=\"fieldValue\">" . $value . "</span>";
                echo "</div>\n";
            }
        ?>
    </div>
    </fieldset>
    
    <?php
        foreach($params as $name => $value) {
            echo "<input type=\"hidden\" name=\"" . $name . "\" value=\"" . $value . "\"/>\n";
        }
    ?>

    <input type="hidden" name="signature" value="<?php echo sign($params) ?>" />
    <input type="submit" id="btn_submit" value="Confirm"/>

</form>

</body>
</html>