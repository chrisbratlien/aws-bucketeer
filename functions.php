<?php

define('APP_PATH', dirname(__FILE__));
define('DATA_PATH', APP_PATH . '/data');


require_once('core.php');
require_once('environment.php');
require_once('functions-curl.php');
require_once('functions-router.php');
require_once('functions-routes.php');

require_once('functions-debug.php');
require_once('functions-curl.php');
require_once('functions-aws.php');
require_once('functions-solr.php');



function slugify_string($str) {
  $result = strtolower($str);
  $result = preg_replace('/\ /', '-', $result);
  $result = preg_replace('/\//', '-', $result);
  $result = preg_replace('/\:/', '-', $result);
  $result = preg_replace('/\./', '-', $result);
  $result = preg_replace('/\-+/', '-', $result);
  return $result;
}
function organize_file($src, $meta = []) {

  $hash = hash_file('sha256', $src);
  //error_header(500,"filename=$filename");
  if (!is_solr_configured()) {
    return pp('no solr');
  }
  $opts = [
    'src' => $src,
    'filename' => $meta['name'],
    'sha256' => $hash
  ];

  $opts = array_merge($opts, $meta);

  $solr_response = solr_update_extract($opts);

  if (isset($solr_response->error)) {
    return [$solr_response->error, false];
  }

  //pp($result, 'res1');
  $bucket = AWS_BUCKET;
  $dest = "s3://${bucket}/sha256/${hash}";
  $size = upload_to_s3($src, $dest);

  //return [error,result]

  return [false, [
    'size' => $size,
    'sha256' => $hash,
    'solr_response' => $solr_response
  ]];
}
