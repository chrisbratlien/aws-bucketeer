<?

require_once(APP_PATH . '/lib/aws/aws-autoloader.php');

$key = getenv('AWSAccessKeyId');
$secret = getenv('AWSSecretKey');

$region = getenv('AWS_REGION');

$credentials = new Aws\Credentials\Credentials($key, $secret);

$options = [
  'version' => 'latest',
  'region'            => $region,
  'credentials' => $credentials
];

$client = new Aws\S3\S3Client($options);

// Register the stream wrapper from an S3Client object
$client->registerStreamWrapper();

$x = 123;

function upload_to_s3($from_local_file, $to_s3_bucket_key_url) {
  if (substr($to_s3_bucket_key_url, 0, 5) !== 's3://') {
    return "bad dest";
  }
  $stream = fopen($to_s3_bucket_key_url, 'w');
  $data = file_get_contents($from_local_file);
  $result = fwrite($stream, $data);
  fclose($stream);
  return $result;
}


function s3_path_of_hash($hash) {
  $bucket = AWS_BUCKET;
  return "s3://${bucket}/sha256/${hash}";
}

//should this be in functions.php instead ?
function serve_asset($sha256, $opts = ['attachment' => false]) {
  //get the actual document
  $path = s3_path_of_hash($sha256);
  //pp($path,'path');
  //exit;
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
