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
