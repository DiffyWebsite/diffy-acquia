<?php

/**
 * @file
 * Trigger compare jobs using Diffy API.
 *
 * @see https://diffy.website
 */

list($script, $site, $target_env, $source_branch, $deployed_tag,) = $argv;

// Project ID. Copy project ID from your project URL.
define('DIFFY_PROJECT_ID', getenv('DIFFY_PROJECT_ID') ? getenv('DIFFY_PROJECT_ID') : '');

// Your API key. Create key in Diffy UI: Profile -> Keys -> Add key.
define('DIFFY_API_KEY', getenv('DIFFY_API_KEY') ? getenv('DIFFY_API_KEY') : '');

// Default base environment to compare to. Defaults to 'prod'.
define('DIFFY_BASE_ENVIRONMENT', getenv('DIFFY_BASE_ENVIRONMENT') ? getenv('DIFFY_BASE_ENVIRONMENT') : 'prod');

if (empty(DIFFY_API_KEY)) {
  print 'Please provide DIFFY_API_KEY' . PHP_EOL;
  exit(1);
}

if (empty(DIFFY_PROJECT_ID)) {
  print 'Please provide DIFFY_PROJECT_ID' . PHP_EOL;
  exit(1);
}

if (empty(DIFFY_BASE_ENVIRONMENT)) {
  print 'Please provide DIFFY_BASE_ENVIRONMENT' . PHP_EOL;
  exit(1);
}

switch ($target_env) {
  case 'dev':
    $environments = DIFFY_BASE_ENVIRONMENT . '-dev';
    break;

  case 'test':
    $environments = DIFFY_BASE_ENVIRONMENT . '-stage';
    break;

  default:
    exit('Running in Neither Dev nor Test environment. No comparision jobs will be triggered.');
}

print "Getting access token from the key." . PHP_EOL;
$ch = curl_init();
$curl_opts = [
  CURLOPT_URL => 'https://diffy.website/api/auth/key',
  CURLOPT_HTTPHEADER => [
    'Accept: application/json',
    'Content-Type: application/json',
  ],
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => json_encode([
    'key' => DIFFY_API_KEY,
  ]),
  CURLOPT_RETURNTRANSFER => TRUE,
  CURLOPT_FAILONERROR => TRUE,
];
curl_setopt_array($ch, $curl_opts);

$curl_response = curl_exec($ch);
// Check if the curl request succeeded.
if ($curl_response === FALSE) {
  print_r(curl_error($ch));
  print PHP_EOL;
  print_r(var_export(curl_getinfo($ch), TRUE));
  print PHP_EOL;
  curl_close($ch);
  exit(1);
}

print 'Successfully retrieved access token from the key.' . PHP_EOL;
$curl_response = json_decode($curl_response);
curl_close($ch);

$token = $curl_response->token;

print 'Starting compare job.';
$ch = curl_init();
$curl_opts = [
  CURLOPT_URL => 'https://diffy.website/api/projects/' . DIFFY_PROJECT_ID . '/compare',
  CURLOPT_HTTPHEADER => [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token,
  ],
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => json_encode([
    'environments' => $environments,
  ]),
  CURLOPT_RETURNTRANSFER => TRUE,
  CURLOPT_FAILONERROR => TRUE,
];
curl_setopt_array($ch, $curl_opts);
$curl_response = curl_exec($ch);

if ($curl_response === FALSE) {
  print_r(curl_error($ch));
  print PHP_EOL;
  print_r(var_export(curl_getinfo($ch), TRUE));
  print PHP_EOL;
  curl_close($ch);
  exit(1);
}

$curl_response = json_decode($curl_response);
curl_close($ch);

print 'Compare job has started.' . PHP_EOL;
print 'Check out the result here: https://diffy.website/ui#/diffs/' . str_replace('diff: ', '', $curl_response) . PHP_EOL;
