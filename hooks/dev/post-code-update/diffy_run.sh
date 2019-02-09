#!/bin/sh

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

drush @$site.$target_env cr

cd /var/www/html/${site}.${target_env}/hooks/diffy

eval "/usr/bin/php diffy_trigger_compare_job.php $site $target_env $source_branch $deployed_tag $repo_url $repo_type"