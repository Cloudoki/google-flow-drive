<?php
namespace Cloudoki\Flowdrive;

/**
 *	Drive API Generic calls
 *	Various repetitive requests.
 */
class DriveLayer
{
	/**
	 *	Drive Get Profile
	 */
	public function getProfile ()
	{
		// Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		$about = $service->about->get();		
		
		return json_encode (
			
			isset ($about['login'])?
				$about:
				['profile'=> $about->getName ()]
		);
	}
	
		
	/**
	 *	Drive Get Folders
	 */
	public function getFolders ($fileId, $and_query = null, $service = null, $limit = null)
	{
		// Extend query
		$query = "mimeType='application/vnd.google-apps.folder'" . ($and_query? ' and ' . $and_query: null);
		
		$results = $this->listFiles ($query, $fileId, $service = null, $limit = null);
		
		return $results->getItems();
		/*
		
		$params = ['q'=> "mimeType='application/vnd.google-apps.folder'" . ($and_query? ' and ' . $and_query: null)];
		
		if($limit)
			
			$params['maxResults'] = $limit;
		
		
		// Drive service
		if (!$service)
		{
			// Get the API client and construct the service object.
			$client = $this->getClient();
			$service = new \Google_Service_Drive($client);
		}
		
		$list = [];
		$results = $service->children->listChildren($fileId, $params);
		
		// Iterate the base folders
		foreach ($results->getItems() as $file)
		{
			$file = $service->files->get($file->getId ());
			
			$list[$file->getId ()] = $file->getTitle();
		}
		
		return $list;*/
	}
	
	/**
	 *	Drive Get 1 Folder
	 */
	public function getFolder ($fileId, $and_query = null, $service = null)
	{
		$results = $this->getFolders ($fileId, $and_query, $service, 1);
		
		return count($results)? $results[0]: null;
	}
	
	/**
	 *	Create Folder
	 */
	public function createFolder ($title)
	{
		$file = new \Google_Service_Drive_DriveFile();
		$file->setTitle ($title);
		$file->setMimeType ('application/vnd.google-apps.folder');
		
		/*$parent = new \Google_Service_Drive_ParentReference();
		$parent->setId('root');
		$file->setParents([$parent]);
		
		$data = file_get_contents($filename);

	    $createdFile = $service->files->insert($file, array(
	      'data' => $data,
	      'mimeType' => $mimeType,
	    ));*/
		
		# WP crap
		wp_die();
	}
	
	/**
	 *	Drive Create File
	 */
	public function createFile ($title, $parentId, $mimeType = 'text')
	{
		// Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		
		$file = new \Google_Service_Drive_DriveFile();
		$file->setTitle ($title);
		$file->setMimeType ($mimeType);
		
		$parent = new \Google_Service_Drive_ParentReference();
		$parent->setId($parentId);
		$file->setParents([$parent]);
		
		$data = "basics\nand stuff";

	    $createdFile = $service->files->insert($file, array(
	      'data' => $data,
	      'mimeType' => $mimeType,
	    ));
		
		# WP crap
		wp_die();
	}
	
	/**
	 *	Get folder contents
	 */
	public function listFiles ($query, $parentId = null, $service = null, $limit = null)
	{
		
		// Extend query
		$params = ['q'=> "trashed=false" . ($query? " and " . $query: null)];
		
		if ($parentId)	$params['q'] .= " and '{$parentId}' in parents";
		if ($limit)		$params['maxResults'] = $limit;
		
		// Drive service
		if (!$service)
		{
			// Get the API client and construct the service object.
			$client = $this->getClient();
			$service = new \Google_Service_Drive($client);
		}
		
		return $service->files->listFiles ($params);
	}
	
	
	/**
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 */
	public function getClient ()
	{
		$client = new \Google_Client();
		$client->setApplicationName(APPLICATION_NAME);
		$client->setScopes(SCOPES);
		$client->setAuthConfigFile(CLIENT_SECRET_PATH);
		$client->setAccessType('offline');
		
		// Load previously authorized credentials from a file.
		$credentialsPath = $this->expandHomeDirectory(CREDENTIALS_PATH);
		if (file_exists($credentialsPath)) {
			$accessToken = file_get_contents($credentialsPath);
		} else {
			
			return ["login"=> $client->createAuthUrl()];
			
			// Request authorization from the user.
			//$authUrl = $client->createAuthUrl();
			//printf("Open the following link in your browser:\n%s\n", $authUrl);
			//print 'Enter verification code: ';
			//$authCode = "4/3Osy8entunUDYEC-tamSMtjOkF7_ckxZ99_J65gXqpk#";//trim(fgets(STDIN));
			
			// Exchange authorization code for an access token.
			$accessToken = $client->authenticate(AUTHCODE);
			
			// Store the credentials to disk.
			if(!file_exists(dirname($credentialsPath))) {
			mkdir(dirname($credentialsPath), 0700, true);
			}
			file_put_contents($credentialsPath, $accessToken);
			printf("Credentials saved to %s\n", $credentialsPath);
		}
		$client->setAccessToken($accessToken);
		
		// Refresh the token if it's expired.
		if ($client->isAccessTokenExpired()) {
			$client->refreshToken($client->getRefreshToken());
			file_put_contents($credentialsPath, $client->getAccessToken());
		}
		return $client;
	}
			
	/**
	* Expands the home directory alias '~' to the full path.
	* @param string $path the path to expand.
	* @return string the expanded path.
	*/
	public function expandHomeDirectory($path)
	{
		$homeDirectory = getenv('HOME');
		if (empty($homeDirectory)) {
			$homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
		}
		
		return str_replace('~', realpath($homeDirectory), $path);
	}
}

?>