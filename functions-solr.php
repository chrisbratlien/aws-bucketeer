<?php
function is_solr_configured() {
  if (empty(getenv('SOLR_HOST'))) {
    return false;
  }
  return true;
}

function solr_update_extract($opts = ['sha256' => '']) {
  if (!is_solr_configured()) {
    pr('solr not configured');
    return false;
  }
  $src = $opts['src'];
  if (!file_exists($src)) {
    die($src . ' does not exist');
  }
  $sha256 = $opts['sha256'];
  if (empty($sha256)) {
    $data = file_get_contents($src);
    $sha256 = hash('sha256', $data);
  }

  $host = getenv('SOLR_HOST');
  $port = getenv('SOLR_PORT');
  $core = getenv('SOLR_CORE');
  $url = "http://$host:$port/solr/$core/update/extract?overwrite=true&wt=json&literal.id=$sha256&literal.sha256=$sha256";

  $url .= '&literal.filename=' . urlencode($opts['filename']);
  $url .= '&literal.mime_type=' . $opts['type'];
  $url .= '&commit=true';

  $curlFile = curl_file_create($src);
  $post = array('file' => $curlFile);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //don't output directly to browser.
  $result_json = curl_exec($ch);
  curl_close($ch);

  $result = json_decode($result_json);
  return $result;
}
