<?php

/**
 * Helper.php
 */
namespace App\Helpers;

use Firebase\JWT\JWT;
use Slim\Psr7\UploadedFile;

class Helper
{
	public $media_path;
	
	function __construct()
	{
		$this->media_path = __DIR__ . '/../media';
	}

	public function toJSON($response, $content, $status) {

	    $response->getBody()->write(json_encode($content, true));
	    return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
	}

	function generateToken($userCred)
	{
		$payload = [
			"username" => $userCred['email'],
			"password" => $userCred['password']
		];
		$secret = 'Wellpr0tecTedSecreKEy.';
		return JWT::encode($payload, $secret, "HS256");
	}

	function moveUploadedFile($directory, UploadedFile $uploadedFile)
	{
		$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
		$basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
		$filename = sprintf('%s.%0.8s', $basename, $extension);

		$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

		return $filename;
	}
}