<?php
class one45API
{
	private $base_url;
	private $public_api_url;
	public $regular_api_url;
	private $client_key;
	private $client_secret;
	private $curl_http_headers = array();
	private $session_id = null;
	private $authorization = null;
	private $access_token = null;
	private $curl_headers = array();
	private $default_limit = null;

	public function __construct($key_master)
	{
		$this->base_url = $key_master->getBaseUrl();
		$this->public_api_url = $key_master->getPublicAPIUrl();
		$this->regular_api_url = $key_master->getRegularAPIUrl();
		$this->client_key = $key_master->getClientKey();
		$this->client_secret = $key_master->getClientSecret();
		$this->default_limit = 50;
		
		$this->session_id = md5('hello world' . time());
	}


	/**
	 * Gets you the authorization header value for your CURL request
	 */
	public function setAuthorization()
	{
		// @TODO We don't deal with a case where during your session the token expires
		if (is_null($this->access_token))
		{
			$response = $this->getToken();
			$this->access_token = $response->access_token;
			$this->authorization = "Bearer " . $this->access_token;
		}
	}
	
	
	/**
	 * Set HTTP Headers we're going to need
	 */
	private function setCurlHeaders($nontoken_request = true)
	{
		// we'll always use a session id
		$this->curl_headers = array
		(
			"Cookie: PHPSESSID=" . $this->session_id
		);
		
		// for requests that don't involve grabbing a token, we need to provide some extra data
		if ($nontoken_request)
		{
			$this->curl_headers[] = "Authorization: " . $this->authorization;
			$this->curl_headers[] = "Content-Type: multipart/form-data; charset=utf-8;";
		}
		else
		{
			$this->curl_headers[] = "Content-Type: application/json; charset=utf-8";
		}
	}
	
	
	/**
	 * Check to see if our string is json - used to help process responses from curl requests
	 */
	private function isJSONString($string)
	{
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	
	
	/**
	 * Convenience method for generating long, array-like get variable strings
	 */
	public static function generateArrayGetVariable($array_name, $variables)
	{
		$get_string = "";
		$cur_variable_num = 0;
		
		foreach ($variables as $variable)
		{
			if ($cur_variable_num)
			{
				$get_string .= "&";
			}

			// %5B is [ and %5D is ]
			$get_string .= $array_name . "%5B" . $cur_variable_num . "%5D=" . $variable;
			$cur_variable_num++;
		}
		
		return $get_string;
	}
	
	
	/**
	 * Process curl responses into objects, or throw errors if there are problems
	 */
	private function processResponse($response, $curl_obj, $write_to_file = false)
	{
		if (!$response)
		{
			die ('Curl Error: "' . curl_error($curl_obj) . '" - Code: ' . curl_errno($curl_obj));
		}
		else if (!$this->isJSONString($response))
		{
			die ('<div>Bad response error:</div><div>' . $response . "</div>");
		}
		
		curl_close($curl_obj);
		
		if ($write_to_file)
		{
			file_put_contents($write_to_file, $response . "\n", FILE_APPEND);
			$decoded = json_decode($response, true);
			$big_array = array_fill(0, count($decoded), "0");

			return $big_array;
		}
		
		else
		{
			return json_decode($response);
		}
	}
	
	/**
	 * Make a curl request
	 */
	private function makeCurlRequest($url, $write_to_file = false, $request_type = "GET", $request_params = null)
	{	
		// Get cURL resource
		$ch = curl_init();
		

		// Set url
		curl_setopt($ch, CURLOPT_URL, $url);

		// Set method
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_type);
		
		// Set options
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		// Set headers
		curl_setopt
		(
			$ch,
			CURLOPT_HTTPHEADER,
			$this->curl_headers
		);
		
		if ($request_params)
		{
			$body = json_encode($request_params);
			
			// Set body
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		}

		// Send the request & save response to $resp
		$resp = curl_exec($ch);
		
		return $this->processResponse($resp, $ch, $write_to_file);
	}
	
	
	/**
	 * This is a generic method for sending a request.
	 * The method handles setting authorization, and curl headers so you don't have to worry about it elsewhere.
	 * It also handles any looping/need for multiple requests to get all the data you have asked for.
	 *
	 * @TODO: What happens if you make a request that finds no results?
	 */
	public function makeGenericRequest($request_url, $args, &$all_responses = null, &$offset = 0)
	{
		//limit 50
		// write_to_file false
		$this->setAuthorization();
		$this->setCurlHeaders();
		
		// this limit, write_to_file and args stuff is to let the user optionally pass an associative array of limit/write_to_file
		$limit = $this->default_limit;
		$write_to_file = false;
		
		extract($args);
		
		$responses = $this->makeCurlRequest($request_url . "&limit=" . $limit . "&offset=" . $offset, $write_to_file);
		file_put_contents("log.txt", "Generic request\n", FILE_APPEND);
		
		// if we have gotten all the possible responses
		if (count($responses) < $limit)
		{
			// if this is our first time through the loop
			if (is_null($all_responses))
			{
				return $responses;
			}
			else
			{
				$all_responses = array_merge($all_responses, $responses);
				
				return $all_responses;
			}
		}
		else // remember the responses we've gotten so far, and go back for some more
		{
			$offset += $limit;
			
			// if this is our first time through the loop
			if (is_null($all_responses))
			{
				$all_responses = $responses;
			}
			else
			{
				$all_responses = array_merge($all_responses, $responses);
			}
			
			return $this->makeGenericRequest($request_url, $args, $all_responses, $offset);
		}
	}


	/**
	 * Get an authorization token
	 */
	public function getToken()
	{
		$this->setCurlHeaders(false);
		
		$request_params = array
		(
			'client_key' => $this->client_key,
			'client_secret' => $this->client_secret
		);
		
		return $this->makeCurlRequest($this->public_api_url . "v1/token/generate", false, "POST", $request_params);
	}
}
?>