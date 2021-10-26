<?php

list($http_status, $headers_array, $body, $error)  = get_cached_or_fetch([
  'cached_filename' => DATA_PATH . '/cached-train-web-jpg',
  'timeout_seconds' => 3600,
  'url' => 'https://rail-safety-tool.tti.tamu.edu/images/train-web.jpg'
]);


header('Content-Type: image/jpg');
echo $body;
