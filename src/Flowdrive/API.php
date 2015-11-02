<?php
namespace Cloudoki\Flowdrive;

class API
{
	/**
	 *	Ajax Calls
	 *
	 *
	 *	Get the base folders
	 */
	public function getProfile ()
	{
		// Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		$about = $service->about->get();		
		
		echo json_encode (
			
			isset ($about['login'])?
				$about:
				['profile'=> $about->getName ()]
		);
				
		# WP crap
		wp_die();
	}
	
	/**
	 *	Get the base folders
	 */
	public function getBaseFolders ()
	{
		
		// Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		$list = [];
		
		$results = $service->children->listChildren('root',
		[
			'q'=> "mimeType='application/vnd.google-apps.folder'"
		]);
		
		// Iterate the base folders
		foreach ($results->getItems() as $file)
		{
			$file = $service->files->get($file->getId ());
			
			$list[$file->getId ()] = $file->getTitle();
		}
		
		echo json_encode ($list);
		
		# WP crap
		wp_die();
	}
	
	/**
	 *	Get the Compare base
	 */
	public function getLayer ()
	{
		$parent = $_GET['folder'];
		
		// Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		
		$list = [];
		
		$results = $service->children->listChildren($parent,
		[
			'q'=> "mimeType='application/vnd.google-apps.folder'"
		]);
		
		// Iterate the base folders
		foreach ($results->getItems() as $file)
		{
			$file = $service->files->get($file->getId ());
			
			$list[$file->getId ()] = $file->getTitle();
		}
		
		echo json_encode ($list);
		
		# WP crap
		wp_die();
	}
	
	/**
	 *	Create File
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
	 *	Get Folder
	 */
	public function getFolder ($title)
	{
		
		// Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		
		$results = $service->children->listChildren('root',
		[
			'maxResults' => 1,
			'q'=> "mimeType='application/vnd.google-apps.folder' and title='{$title}'"
		]);
		
		// Iterate the base folders
		foreach ($results->getItems() as $file)
		
			echo $file->getId ();
		
		# WP crap
		wp_die();
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
	 *	Select items list
	 */
	public function compare ()
	{
		// Dynamics
		$list = [];
		
		
		// Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		
		// NON_DYNAMIC FOR NOW
		// POSTTYPE LEVEL
		/*$results = $service->children->listChildren($_GET['selected'], [
			'q'=> "mimeType='application/vnd.google-apps.folder' and title = 'Production'"
		]);
		$results = $results->getItems();
		
		$results = $service->children->listChildren($results[0]->getId (), [
			'q'=> "mimeType='application/vnd.google-apps.folder' and title = 'content'"
		]);
		$results = $results->getItems();
		
		echo $results[0]->getId ();
		wp_die();*/
		
		// POSTTYPE
		$results = $service->children->listChildren("0B28Pui76yHNeMGhpcGNTRmZlVG8", [
			'q'=> "mimeType='application/vnd.google-apps.folder' and title = '" . $_GET['flag'] . "'"
		]);
		$results = $results->getItems();
		
		// CATEGORIES
		$results = $service->children->listChildren($results[0]->getId (), [
			'q'=> "mimeType='application/vnd.google-apps.folder'"
		]);
		
		foreach ($results->getItems() as $cat)
		{
			$folder = $service->files->get($cat->getId ());
			$catname = $folder->getTitle();
			
			$list[$catname] = [];
			
			$subresults = $service->children->listChildren($cat->getId (), [
				'q'=> "mimeType='application/vnd.google-apps.folder'"
			]);
			
			foreach ($subresults->getItems() as $file)
			{
				$file = $service->files->get($file->getId ());
				
				$list[$catname][$file->getId ()] = [
					'title'=> $file->getTitle()
				];	
			}
		}
		
		$posttypes = [
			'advertorial'=> 'advertorial',
			'agenda'=> 'calendar',
			'articles'=> 'post',
			'production'=> 'product'
		];
		

		foreach ($list as $catlist)
			foreach ($catlist as $item)
			{
				$compare = new \WP_Query([
					'posts_per_page' => 1,
					'post_type' => $posttypes[$_GET['flag']],
					'title' => $item['title']
				]);
				
				if($compare->have_posts())
				{	
					$compare->the_post();
					$item['wp_id'] = get_the_ID ();
					$itrm['post_type'] = $posttypes[$_GET['flag']];
				}
			}
		
		//$list['post_type'] = $posttypes[$_GET['flag']];
		
		echo json_encode ($list);
		
		# WP crap
		wp_die();

	}
	
	/**
	 *	Get folder contents
	 */
	public function getFolderContents ()
	{
		// Dynamics
		$list = (object)[
			'title'=> $_GET['title'],
			'folderId'=> preg_replace('/\s+/', '', $_GET['folderId']),
			'max_attach'=> (int) get_option("flowdrive_max_attach"),
			'media'=> [],
			'text'=> []
		];
		
		// Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		
		// Get media files
		$mediafolder = $service->children->listChildren($list->folderId, [
			'q'=> "mimeType='application/vnd.google-apps.folder' and (title = 'images' or title = 'media')"
		]);
		$mediafolder = $mediafolder->getItems();
		
		if (count ($mediafolder))
		{
			$mediafiles = $service->files->listFiles([
				'q'=> "(mimeType='image/jpeg' or mimeType='image/png' or mimeType='image/gif') and '" . $mediafolder[0]->getId() . "' in parents and trashed=false"
			]);
			
			foreach ($mediafiles->getItems() as $file)
			
				$list->media[$file->getId ()] = ['title'=> $file->getTitle (), 'thumbnail'=> $file->getThumbnailLink ()];	
		}
		
		// Get content download
		//$contents = $service->children->listChildren($_GET['folderId'], [
		$contents = $service->files->listFiles([
			'q'=> "(mimeType='text/plain' or mimeType='text/markdown' or mimeType='text/richtext' or mimeType='application/rtf' or mimeType='application/msword') and '{$list->folderId}' in parents and trashed=false"
		]);
		
		foreach ($contents->getItems() as $file)
		
			$list->text[$file->getId ()] = [
				'title'=> $file->getTitle (), 
				'download'=> $file->getDownloadUrl ()
			];	
		

		echo json_encode ($list);
		
		# WP crap
		wp_die();

	}
	
	/**
	 *	Post a Flow Item
	 */
	public function postItem ()
	{
		// Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		
		// Get the public folder
		$publicId = $this->get_public_folder ($service);
	
		// Dynamics
		$post = [
			'post_title'=> wp_strip_all_tags($_POST['title']),
			'post_name'=> sanitize_title($_POST['title']),
			'post_content'=> '',
			'post_status'=> 'pending',
			'post_type'=> $_POST['post_type']?: 'calendar', // ¡HACK!
			'post_category'=> []
		];
		
		// Collect categories
		foreach($_POST['cats'] as $cat) 
			
			if ($cat && $cat != 'undefined') 
				if ($catid = term_exists ($cat)) $post['post_category'][] = (int) $catid;

		
		// Collect main content
		$files = (object) $_POST['files'];

		if (isset ($files->main))
		{	
			$request = new \Google_Http_Request($files->main, 'GET', null, null);
			$httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
			$post['post_content'] = $httpRequest->getResponseBody();
		}
		
		// Collect excerpt content
		if (isset ($files->excerpt))
		{	
			$request = new \Google_Http_Request($files->excerpt, 'GET', null, null);
			$httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
			$post['post_excerpt'] = $httpRequest->getResponseBody();
		}

		// Insert Post
		$post_id = wp_insert_post ($post, false);

		// Attach media
		if(count ($_POST['media']))
		
			foreach ($_POST['media'] as $fileId) $this->insert_attachment ($service, $fileId, $post_id, $publicId);
			
			
		
						
		echo json_encode (['id'=> $post_id]);
		
		# WP crap
		wp_die();

	}
	
	public function insert_attachment ($service, $fileId, $post_id, $publicId)
	{
		// Original file
		$file = $service->files->get($fileId);
		$title = preg_replace( '/\.[^.]+$/', '', $file->getTitle());
		$parents = $file->getParents();
		
		// Set base folder
		if ($publicId)
		{
			$parent = new \Google_Service_Drive_ParentReference();
			$parent->setId($publicId);

			// Check if already public
			if(!in_array ($parent, $parents))
			{	
				$parents[] = $parent;
				
				// Add to public
				$file->setParents ($parents);
				$file = $service->files->update ($fileId, $file);
				
				// Make View all
				$perm = new \Google_Service_Drive_Permission();
				$perm->setType ('anyone');
				$perm->setRole ('reader');
				
				$service->permissions->insert($fileId, $perm);
			}
		}

		// Attach to post
		$attach = [
			'guid'           => "https://googledrive.com/host/" . $publicId . "/" . $file->getTitle(), 
			'post_mime_type' => $file->getMimeType (),
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit'
		];
		
		wp_insert_attachment ($attach, $title, $post_id);
	}
	
	public function get_public_folder ($service)
	{
		$publicId = get_option("flowdrive_public", false);
		
		if (!$publicId) 
		{
			$publicId = $this->insert_public_folder ($service);
			
			if($publicId === false)		add_option ("flowdrive_public", $publicId);
			else						update_option ("flowdrive_public", $publicId);
		}
		
		return $publicId;
	}
	
	public function insert_public_folder ($service)
	{
		$parentId = get_option ("flowdrive_basefolder");
		
		$file = new \Google_Service_Drive_DriveFile();
		$file->setTitle('flowdrive');
		$file->setMimeType('application/vnd.google-apps.folder');
		
		// Set base folder
		if ($parentId) {
			$parent = new \Google_Service_Drive_ParentReference();
			$parent->setId($parentId);
			$file->setParents([$parent]);
		}
		
		$createdFile = $service->files->insert($file, ['mimeType' => 'application/vnd.google-apps.folder']);
		
		$permission = new \Google_Service_Drive_Permission();
		$permission->setValue('');
		$permission->setType('anyone');
		$permission->setRole('reader');
		
		$service->permissions->insert($createdFile->getId(), $permission);
		
		return $createdFile->getId();
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
			$authCode = "4/3Osy8entunUDYEC-tamSMtjOkF7_ckxZ99_J65gXqpk#";//trim(fgets(STDIN));
			
			// Exchange authorization code for an access token.
			$accessToken = $client->authenticate($authCode);
			
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
	
	public function storeClient ()
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
			
			// Request authorization from the user.
			//$authUrl = $client->createAuthUrl();
			//printf("Open the following link in your browser:\n%s\n", $authUrl);
			//print 'Enter verification code: ';
			$authCode = "4/3Osy8entunUDYEC-tamSMtjOkF7_ckxZ99_J65gXqpk#";//trim(fgets(STDIN));
			
			// Exchange authorization code for an access token.
			$accessToken = $client->authenticate($authCode);
			
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