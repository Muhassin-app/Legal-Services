<html>

<head>
<title>Hellow World</title>
</head>
<body>


<?php
error_reporting(0);
$zillow_id = 'X1-ZWz16b0yk0045n_8mfo';

$search = '155 Demar Blvd';
$citystate = 'Canonsburg PA';
$address = urlencode($search);
$citystatezip = urlencode($citystate);

$url = "http://www.zillow.com/webservice/GetDeepSearchResults.htm?zws-id=$zillow_id&address=$address&citystatezip=$citystatezip";

$result = file_get_contents($url);
$data = simplexml_load_string($result);
echo $result;
echo $data;
echo $data->response->results->result->lotSizeSqFt . "<br>";

?>

</body>
</html>