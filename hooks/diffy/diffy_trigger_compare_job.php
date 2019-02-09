<?php
/**
 * Script to trigger compare jobs on Diffy API.
 * @see https://diffy.website for more details.
 **/
list($script, $site, $target_env, $source_branch, $deployed_tag, ) = $argv;

// Settings. Please provide your own here.
$api_key = 'AAA';
$project_id = 111;

// Choose what environments to compare.
switch ($target_env) {
  case 'dev':
    $operation = 'prod-dev';
    break;
  case 'test':
    $operation = 'prod-stage';
    break;
  default:
    echo 'Neither Dev nor Test environment. Do not trigger any job.';
    exit;
}

// Get access token from key.
$ch = curl_init();
$curl_options = array(
  CURLOPT_URL => 'https://diffy.website/api/auth/key',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Content-Type: application/json'
  ),
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => json_encode(array(
    'key' => $api_key
  )),
  CURLOPT_RETURNTRANSFER => TRUE,
);
curl_setopt_array($ch, $curl_options);
$curl_response = json_decode(curl_exec($ch));
curl_close($ch);

$token = $curl_response->token;

// Run Compare job.
$ch = curl_init();
$curl_options = array(
  CURLOPT_URL => 'https://diffy.website/api/projects/' . $project_id . '/compare',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token,
  ),
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => json_encode(array(
    'environments' => $operation,
  )),
  CURLOPT_RETURNTRANSFER => TRUE,
);
curl_setopt_array($ch, $curl_options);
$curl_response = json_decode(curl_exec($ch));
curl_close($ch);

echo "Compare job has started.\n";
echo 'Check out the result here: https://diffy.website/ui#/diffs/' . str_replace('diff: ', '', $curl_response) . "\n";
