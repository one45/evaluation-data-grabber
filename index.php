<?php
require_once('genericKeyMaster.php');
require_once('one45api.php');
require_once('evaluationDataGrabber.php');

/**
 * This function goes and grabs data for a given from/date range
 * 
 * It gets the completed evaluations, the questions on the form, and the answers to the evaluations
 */
function getEvaluationData($data_grabber, $form_id, $date_range_to_get_data_from)
{
	echo "<h1>Lets go and get some evaluation data</h1>";
	file_put_contents('log.txt', 'Getting started.');

	$start_time = microtime(true);
	$evaluations = $data_grabber->getFormEvaluations($form_id, $date_range_to_get_data_from);
	$end_time = microtime(true);

	$execution_time = $end_time - $start_time;
	$total_execution_time = $execution_time;

	$evaluation_ids = array();

	echo "<h2>Evaluations</h2>";
	echo "<div>There were " . count($evaluations) . " evaluations.</div>";
	echo "<div>It took " . $execution_time . " seconds to get the evaluations</div>";

	file_put_contents('log.txt', '\nThere are ' . count($evaluations) . ' evaluations to get data for.', FILE_APPEND);

	foreach ($evaluations as $evaluation)
	{
		$evaluation_ids[] = $evaluation->evaluation_id;
	}

	$start_time = microtime(true);
	$questions = $data_grabber->getFormQuestions($form_id);
	$end_time = microtime(true);

	$execution_time = $end_time - $start_time;
	$total_execution_time += $execution_time;

	echo "<h2>Questions</h2>";
	echo "<div>There were " . count($questions) . " questions.</div>";
	echo "<div>It took " . $execution_time . " seconds to get the questions</div>";

	file_put_contents('log.txt', '\nThere are ' . count($questions) . ' questions on the form.', FILE_APPEND);

	$start_time = microtime(true);
	$answers = $data_grabber->getAnswersForEvaluations($evaluation_ids);
	$end_time = microtime(true);

	$execution_time = $end_time - $start_time;
	$total_execution_time += $execution_time;

	echo "<h2>Answers</h2>";
	echo "<div>There were " . count($answers) . " answers.</div>";
	echo "<div>It took " . $execution_time . " seconds to get the answers</div>";

	file_put_contents('log.txt', '\nThere are ' . count($answers) . ' answers total.', FILE_APPEND);

	echo "<h2>Final result</h2>";
	echo "<div><b>It took a grand total of $total_execution_time seconds to get all of the data.</b></div>";
	file_put_contents('log.txt', '\nThe whole thing took ' . $total_execution_time . ' seconds.', FILE_APPEND);
}


$key_master = new genericKeyMaster(); // the keymaster is where you store your client_key, client_secret, and the URL for your school
$data_grabber = new evaluationDataGrabber($key_master); // the evaluationDataGrabber is the class that gets assessment data for us
$form_id = 555; // this is the one45 id of the form you want to get data about
$date_range_to_get_data_from = "2015-07-01T05:30:00|2016-06-30T10:30:00"; // the date range we're going to grab data for

getEvaluationData($data_grabber, $form_id, $date_range_to_get_data_from)

?>