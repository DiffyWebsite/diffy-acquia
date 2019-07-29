# Diffy Acquia cloud hooks integration

Automates triggering "compare" job for your Acquia project whenever you do a
deployment.

If you do a deployment to "dev" or "test" environments this script triggers a 
job to compare "dev"/"test" with "prod".

More on Acquia cloud hooks: https://docs.acquia.com/acquia-cloud/develop/api/cloud-hooks/

Diffy project: https://diffy.website

Video tutorial: https://youtu.be/wOuB8tRNNYw

## Installation notes

You would need to have a project created in Diffy (need its ID). Also you need 
to provide API key so the script can authenticate.

Provide these details either as `DIFFY_API_KEY` and `DIFFY_PROJECT_ID` 
environment variables or update [diffy_trigger_compare_job.php](hooks/diffy/diffy_trigger_compare_job.php) file.
