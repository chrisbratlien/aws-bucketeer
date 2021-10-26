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


//should this be in functions.php instead ?
function serve_asset($sha256, $opts = ['attachment' => false]) {

  $cached_filename = DATA_PATH . '/cached-' . $sha256;
  $content = get_cached([
    'cached_filename' => $cached_filename,
    'timeout_seconds' => -1 //cache forever
  ]);

  if (!empty($content)) {
  }

  file_put_contents($opts->cached_filename, $body);

  //get the actual document
  $path = s3_path_of_hash($sha256);
  //pp($path,'path');
  //exit;
}
function serve_path($path) {
  if (empty($path) || !file_exists($path)) {
    error_404();
  }

  // get the file's mime type to send the correct content type header
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime_type = finfo_file($finfo, $path);


  $public_name = 'Untitled';
  if (isset($opts['title'])) {
    $public_name = $opts['title'];
  }


  // send the headers
  $dispo = $opts['attachment'] ? 'attachment' : 'inline';

  header("Content-Disposition: $dispo; filename=\"$public_name\";");
  header("Content-Type: $mime_type");
  header('Content-Length: ' . filesize($path));

  // stream the file
  $fp = fopen($path, 'rb');
  fpassthru($fp);
  exit;
}
