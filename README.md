# Diffy Acquia cloud hooks integration

Automates triggering "compare" job for your Acquia project whenever you do deployment.

If you do deployment to "dev" or "test" environments this script triggers job to compare dev/test with prod.

More on Acquia cloud hooks: https://docs.acquia.com/acquia-cloud/develop/api/cloud-hooks/

Diffy project: https://diffy.website

## Installation notes

You would need to have a project created in Diffy (need its ID). Also you need to provide API key so script can authenticate. 

Please provide these details https://github.com/DiffyWebsite/diffy-acquia/blob/master/hooks/diffy/diffy_trigger_compare_job.php.
