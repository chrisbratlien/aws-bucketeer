<?php

add_route('/upload', function () {
  ////return pr($_FILES, 'files??');

  if (empty($_FILES)) {
    error_header(401, 'No File');
  }
  $file_meta = $_FILES['file'];
  $src = $file_meta['tmp_name'];
  $res = organize_file($src, $file_meta); //[error, result] format

  //Q: should this return an error_header if the error is populated? 
  //Q: or should it just consistently return a 200 with
  // an [error, result] JSON payload for the API caller to sort out?
  header('Content-Type: application/json');
  echo json_encode($res);
});



add_route('/solr-search', function () {
  $opts = $_GET;
  //FIXME: change solr to the ENV variable for the solr host?
  $base_url = 'http://solr:8983/solr/mycore';
  $query_string = urlencode($opts['q']);
  $full = "${base_url}/select?q=${query_string}";
  $json = file_get_contents($full);
  header('Content-Type: application/json');
  echo $json;
});

//attachment
add_route('/asset/{sha256}', function ($sha256) {
  serve_asset($sha256, ['attachment' => true]);
});

//inline
add_route('/inline/{sha256}', function ($sha256) {
  serve_asset($sha256, ['attachment' => false]);
});
