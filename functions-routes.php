<?php

add_route('/api/milk/{percent}', function ($percent) {
  header('Content-Type: application/json');
  echo json_encode([
    'msg' => "you requested $percent% milk"
  ]);
});


add_route('/milk/{percent}', function ($percent) {
  //pp($percent, 'you requested this percent');
  echo "you requested $percent% milk";
});


//requires functions-curl.php
add_route('/third-party-api', function () {

  //[$status, $headers, $body, $error] = curl_get('https://awesome-third-party-api');
  //pp([$status, $headers, $body, $error], 'what I got back');
});

add_route('/test-aws', function () {

  $bucket = getenv('AWS_BUCKET');
  //write
  //file_put_contents("s3://${bucket}/hello", 'Hello!');

  //read
  $data = file_get_contents("s3://${bucket}/hello");
  pp($data, 'data');
});
