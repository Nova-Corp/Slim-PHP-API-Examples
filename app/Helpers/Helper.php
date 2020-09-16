<?php

/**
 * Helper.php
 */
namespace App\Helpers;

class Helper
{
	
	function __construct()
	{
		# code...
	}

	public function toJSON($response, $content, $status) {

	    $response->getBody()->write(json_encode($content));
	    return $response->withHeader('Content-Type','application/json')->withStatus($status);
	}
}