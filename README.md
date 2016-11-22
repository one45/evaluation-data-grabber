# one45 Evaluation data grabber

## Quick description
This is a PHP tool for using one45 APIs to get assessment data out of one45. It requires knowing the id of the form you want to grab assessment data from, and the date range you care about.

## Setup
1. Create a copy of "genericKeyMaster.php" and add the client_key, client_secret, and URL of your organization in it
2. Make sure that you "require_once" your new keymaster file in index.php
3. Update index.php to specify your new key file, form id, and date range
4. Run the script and the assessment data should roll in!

## Other notes
- The scripts grab data about assessments in one45, but there is nothing (yet) that organizes or displays the actual data
- Most of the logic lives in evaluationDataGrabber.php - that's where all the methods that pull data live
- All of the methods in evaluationDataGrabber allow you to specify that you want to write the data from the APIs to a file. To write to a file, specify a filename using "write_to_file => MYFILE.TXT" in the $args method parameter.
