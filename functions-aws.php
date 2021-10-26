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
