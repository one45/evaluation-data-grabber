<?php
require_once('one45api.php');

/**
 * Class for getting one45 evaluation data
 *
 * NB: For all the methods that get data, the args parameter can accept an associative array with a "limit" and "write_to_file" parameter
 */
class evaluationDataGrabber
{
	private $api = null;
	private $default_limit = null;
	
	public function __construct($key_master = null, $default_limit = 50)
	{
		if (is_null($key_master))
		{
			die("Must pass a keymaster class or we can't grab API data");
		}
		
		$this->api = new one45API($key_master);
		$this->default_limit = $default_limit;
	}
	
	
	/**
	 * Get all of the evaluations for a given form
	 *
	 * NOTE: This uses the received on filter - it might make sense to use the evaluation_starts_on and evaluation_ends_on filters instead
	 */
	public function getFormEvaluations($form_id, $date_range = null, $args = array('limit' => 600))
	{
		$date_range = (is_null($date_range)) ? "" : "&received_on=" . $date_range;

		return $this->api->makeGenericRequest($this->api->regular_api_url . "v1/evaluations?form_ids%5B0%5D=" . $form_id . "&status%5B0%5D=completed" . $date_range, $args);
	}
	
	
	/**
	 * Get information about a given form
	 */
	public function getFormInfo($form_id, $args = array())
	{
		return $this->api->makeGenericRequest($this->api->regular_api_url . "v1/forms/" . $form_id, $args);
	}


	/**
	 * Get all of the questions for a given form
	 */
	public function getFormQuestions($form_id, $args = array('limit' => 1000))
	{
		return $this->api->makeGenericRequest($this->api->regular_api_url . "v1/forms/" . $form_id . "/questions?", $args);
	}
	
	
	/**
	 * Get the answers for a set of evaluations
	 *
	 * @TODO The chunking work here could be pulled out into a more generic method
	 */
	public function getAnswersForEvaluations($evaluations, $args = array('limit' => 200))
	{
		$all_answers = array();
		$chunked_evaluations = array_chunk($evaluations, $args['limit']);

		foreach ($chunked_evaluations as $chunk)
		{
			$get_string = one45API::generateArrayGetVariable('evaluation_ids', $chunk);
			$answers = $this->api->makeGenericRequest($this->api->regular_api_url . "v1/answers?" . $get_string, $args);
	
			foreach ($answers as $answer)
			{
				$all_answers[] = $answer;
			}
		}
		
		return $all_answers;
	}
}
?>