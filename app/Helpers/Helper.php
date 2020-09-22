<?php

/**
 * Helper.php
 */
namespace App\Helpers;

use Exception;
use Firebase\JWT\JWT;
use Slim\Psr7\UploadedFile;

class Helper
{
	public $media_path;
	
	function __construct()
	{
		$this->media_path = __DIR__ . '/../../public/media';
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
		$basename = pathinfo($uploadedFile->getClientFilename(), PATHINFO_FILENAME);
		$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
		$time = time(); // see http://php.net/manual/en/function.random-bytes.php
		$filename = $basename.'_'. $time.'.'.$extension;

		$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

		return array('filename'=> $basename . '_' . $time, 'extension' => $extension);
	}

	function validateInputMedia($formatAllowed, $uploadedFile)
	{
		$filename = $uploadedFile->getClientFilename();
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if (!in_array($ext, $formatAllowed)) {
			return false;
		} else {
			return true;
		}
	}
}