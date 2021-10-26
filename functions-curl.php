<?php

function curl_get($url, array $get = array(), array $options = array()) {

  //pr($url,'url');
  //exit;

  $defaults = array(
    CURLOPT_URL => $url . (strpos($url, '?') === FALSE ? '?' : '') . http_build_query($get),
    CURLOPT_HEADER => TRUE,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_FOLLOWLOCATION => TRUE,
    CURLOPT_CAINFO => dirname(__FILE__) . '/data/cacert.pem',
  );

  $ch = curl_init();
  curl_setopt_array($ch, ($options + $defaults));

  $response = curl_exec($ch);

  // Then, after your curl_exec call:
  $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
  $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $header = substr($response, 0, $header_size);
  $body = substr($response, $header_size);
  $error = false;
  if (!$response) {
    $error = curl_error($ch);
    trigger_error($error);
  }
  curl_close($ch);


  $headers_array = preg_split('/\r|\n/', $header);
  $headers_array = array_filter($headers_array, function ($header) {
    return !empty($header);
  });
  return [$http_status, $headers_array, $body, $error];
}


function have_cached(array $opts = []) {
  $defaults = [
    'cached_filename' => false, //required
    'timeout_seconds' =>  3600,
  ];
  $opts = (object) array_merge($defaults, $opts);

  if (!file_exists($opts->cached_filename)) {
    return false;
  }
  $last_modified = filemtime($opts->cached_filename);
  $now = time();
  $diff_seconds = $now - $last_modified;
  if ($opts->timeout_seconds > -1 && $diff_seconds > $opts->timeout_seconds) {
    return false;
  }
  //cached file is younger than the timeout. We're allowed to reuse cached data.
  return true;
}

function get_cached(array $opts = []) {
  $defaults = [
    'cached_filename' => false, //required
    'timeout_seconds' =>  3600,
  ];
  $opts = (object) array_merge($defaults, $opts);

  if (!have_cached((array) $opts)) {
    return false;
  }
  $content = file_get_contents($opts->cached_filename);
  return $content;
}

function get_cached_or_fetch(array $opts = []) {

  $defaults = [
    'cached_filename' => false, //required
    'timeout_seconds' =>  3600,
    'url' => false, //required
    'headers' => []
  ];
  $opts = (object) array_merge($defaults, (array) $opts);
  //NOTE: opts now converted from an array to an object!
  $body = get_cached((array) $opts);

  if (!empty($body)) {
    $headers_array = [];
    $header_file = $opts->cached_filename . '-headers';
    if (file_exists($header_file)) {
      //$headers = file_get_contents($opts->cached_filename . '-headers');
      $headers_array = file($header_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      $headers_array = array_filter($headers_array, function ($header) {
        return !empty($header);
      });
    }
    return [200, $headers_array, $body, false];
  }

  //CACHE didn't exist or was too old... do a fresh fetch/curl

  $curl_get_options = [];
  if (!empty($opts->headers)) {
    $curl_get_options = array_replace($curl_get_options, [
      CURLOPT_HTTPHEADER => $opts->headers
    ]);
  }

  list($status, $headers_array, $body, $error) = curl_get($opts->url, [], $curl_get_options);

  file_put_contents($opts->cached_filename, $body);

  $headers_string = join("\n", $headers_array);
  file_put_contents($opts->cached_filename . '-headers', $headers_string);
  /**
  cblog([
    'status' => $status,
    'headers' => $headers_array,
    'body' => $body,
    'error' => $error
  ],'[verbose curl_get]');
   **/
  return [$status, $headers_array, $body, $error];
}
