<?php $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://velacafe.dev/frontend/backup");
$output = curl_exec($ch);
curl_close($ch);
curl_close($ch);
echo $output;