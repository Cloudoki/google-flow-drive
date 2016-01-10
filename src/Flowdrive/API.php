<?php
namespace Cloudoki\Flowdrive;

use Cloudoki\Flowdrive\DriveLayer;

class API extends DriveLayer
{
	/**
	 *	Ajax Calls
	 *
	 *
	 *	Get the base folders
	 */
	public function getUserProfile ()
	{
		// Get the API client and construct the service object.
		echo $this->getProfile ();
				
		# WP crap
		wp_die();
	}
	
	/**
	 *	Get the base folders
	 */
	public function getBaseFolders ()
	{
		$list = [];
		$results = $this->getFolders ('root');
		
		// Iterate the base folders
		foreach ($results as $file)
		
			$list[$file->getId ()] = $file->getTitle();
			
		echo json_encode ($list);
		
		# WP crap
		wp_die();
	}
	
	/**
	 *	Get the Compare base
	 */
	public function getLayer ()
	{	
		$list = [];
		$results = $this->getFolders ($_GET['folder'], "title != 'flowdrive'");
		
		// Iterate the base folders
		foreach ($results as $file)
		
			$list[$file->getId ()] = $file->getTitle();
			
		echo json_encode ($list);
		
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
		$flag = $_GET['flag'];
		$selected = $_GET['selected'];
		
		// Hardcoded folders
		$folders = [
			'Production'=> "0B28Pui76yHNeWnpVdjhfWmNUWms",
			'content'=> "0B28Pui76yHNeMGhpcGNTRmZlVG8",
			'advertorial'=> "0B28Pui76yHNeamZ1YkliMmZrX2s",
			'agenda'=> "0B28Pui76yHNeRHNHMUtLZVcyS0k",
			'articles'=> "0B28Pui76yHNedGZhMHhFSnhER3c",
			'production'=> "0B28Pui76yHNecTYzSjRSVGtDb3M"
		];
		
		$posttypes = [
			'advertorial'=> 'advertorial',
			'agenda'=> 'calendar', 
			'articles'=> 'post', 
			'production'=> 'product', 
		];
		
		// Get the API client and construct the service object.
		$client = $this->getClient();
		$service = new \Google_Service_Drive($client);
		
		
		// Cat/Tag folders
		if (in_array ($flag, ['agenda', 'production']))
		{
			$results = $this->getFolders ($folders[$flag], null, $service);
			
			foreach ($results as $category)
			{	
				$catname = $category->getTitle();
				
				$list[$catname] = [];
				
				$subresults = $this->getFolders ($category->getId (), null, $service);
				
				foreach ($subresults as $item)
					
					$list[$catname][$item->getId ()] = [
						'title'=> $item->getTitle(),
						'post_type' => $posttypes [$flag],
						'taxonomy'=> $category->getId (),
						'base'=> $selected
					];	
			}	
		} 
		
		// Direct items
		else 
		{
			$results = $this->getFolders ($folders[$flag], null, $service);
			
			foreach ($results as $item)
					
					$list[0][$item->getId ()] = [
						'title'=> $item->getTitle(),
						'post_type' => $posttypes [$flag],
						'base'=> $selected
					];	
		}
		
		
		// Compare with WP
		foreach ($list as $catlist)
			foreach ($catlist as $item)
			{
				$compare = new \WP_Query([
					'posts_per_page' => 1,
					'post_type' => $posttypes [$flag],
					'title' => $item['title']
				]);
				
				if($compare->have_posts())
				{	
					$compare->the_post();
					$item['wp_id'] = get_the_ID ();
				}
			}
		
		
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
			'q'=> "(mimeType='text/plain' or mimeType='text/markdown' or mimeType='text/richtext' or mimeType='application/rtf' or mimeType='application/msword' or mimeType='application/vnd.openxmlformats-officedocument.wordprocessingml.document') and '{$list->folderId}' in parents and trashed=false"
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
		// Dynamics
		$data = (object) $_POST['data'];
		$files = (object) $_POST['files'];
		
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
			'post_type'=> $data->post_type
		];
		
		// Collect categories
		if(isset ($data->taxonomy))
		{
			$file = $service->files->get ($data->taxonomy);
			if ($catid = term_exists ($file->getTitle())) $post['post_category'] = [(int) $catid];
		}
		
		if(isset ($data->base))
		{
			// Wrong. Should be set_object_terms
			
			//$file = $service->files->get ($data->base);
			//if ($catid = term_exists ($file->getTitle())) $post['post_category'][] = (int) $catid;
		}

		
		
		// Collect main content
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
		
		// DOC/DOCX/PAGES Solution?
		// unzip -p somefile.docx word/document.xml | sed -e 's/<[^>]\{1,\}>//g; s/[^[:print:]]\{1,\}//g'
		
		
		// Insert Post
		$post_id = wp_insert_post ($post, false);
		
		// Add taxonomy - ISSUE 53
		wp_set_object_terms( $post_id, 4422, 'magazine', true);

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
		
		$attach_id = wp_insert_attachment ($attach, $title, $post_id);
		
		add_post_meta( $post_id, '_thumbnail_id', $attach_id, true);
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
}

?>