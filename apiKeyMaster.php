<?php
abstract class apiKeyMaster
{
	protected $public_api_string = "public/api/";
	protected $regular_api_string = "api/";
	
	abstract function getBaseUrl();
	abstract function getClientKey();
	abstract function getClientSecret();
	
	public function getPublicAPIUrl()
	{
		return $this->getBaseUrl() . $this->public_api_string;
	}
	
	public function getRegularAPIUrl()
	{
		return $this->getBaseUrl() . $this->regular_api_string;
	}
}
?>