<?php

/**
 * @file
 * Trigger compare jobs using Diffy API.
 *
 * @see https://diffy.website
 */

list($script, $site, $target_env, $source_branch, $deployed_tag,) = $argv;

// Provide Diffy API key and project id as environment variables or
// override here.
define('DIFFY_API_KEY', getenv('DIFFY_API_KEY') ? getenv('DIFFY_API_KEY') : 'DIFFY_API_KEY_PLACEHOLDER');
define('DIFFY_PROJECT_ID', getenv('DIFFY_PROJECT_ID') ? getenv('DIFFY_PROJECT_ID') : 'DIFFY_PROJECT_ID_PLACEHOLDER');

// Default base environment to compare to. Defaults to 'prod'.
define('DIFFY_BASE_ENVIRONMENT', getenv('DIFFY_BASE_ENVIRONMENT') ? getenv('DIFFY_BASE_ENVIRONMENT') : 'prod');

if (empty(DIFFY_API_KEY)) {
  print 'Please provide DIFFY_API_KEY';
  exit(1);
}

if (empty(DIFFY_PROJECT_ID)) {
  print 'Please provide DIFFY_PROJECT_ID';
  exit(1);
}

if (empty(DIFFY_BASE_ENVIRONMENT)) {
  print 'Please provide DIFFY_BASE_ENVIRONMENT';
  exit(1);
}

// Choose what environments to compare.
switch ($target_env) {
  case 'dev':
    $environments = DIFFY_BASE_ENVIRONMENT . '-dev';
    break;

  case 'test':
    $environments = DIFFY_BASE_ENVIRONMENT . '-stage';
    break;

  default:
    exit('Neither Dev nor Test environment. Do not trigger any jobs.');
}

print "Getting access token from the key.";
$ch = curl_init();
curl_setopt_array($ch, [
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
]);

$curl_response = curl_exec($ch);
// Check if the curl request succeeded.
if ($curl_response === FALSE) {
  $info = var_export(curl_getinfo($ch), TRUE);
  $error = curl_error($ch);
  curl_close($ch);

  print_r($info);
  print_r($error);

  exit(1);
}

print "Successfully retrieved access token from the key.";
$curl_response = json_decode($curl_response);
curl_close($ch);

$token = $curl_response->token;

print "Starting compare job.";
$ch = curl_init();
curl_setopt_array($ch, [
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
]);
$curl_response = curl_exec($ch);

if ($curl_response === FALSE) {
  $info = var_export(curl_getinfo($ch), TRUE);
  $error = curl_error($ch);
  curl_close($ch);

  print_r($info);
  print_r($error);

  exit(1);
}

$curl_response = json_decode($curl_response);
curl_close($ch);

print "Compare job has started.";
print 'Check out the result here: https://diffy.website/ui#/diffs/' . str_replace('diff: ', '', $curl_response);
