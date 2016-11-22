<?php
require_once('apiKeyMaster.php');

/**
 * This class has all the credentials and other details we need to connect to an app 
 */
class genericKeyMaster extends apiKeyMaster
{
	public function getBaseUrl()
	{
		return "https://YOUR URL HERE/web/one45_stage.php/";
	}
	
	public function getClientKey()
	{
		return "YOUR CLIENT KEY";
	}
	
	public function getClientSecret()
	{
		return "YOUR CLIENT SECRET";
	}
}
?>