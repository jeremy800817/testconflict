<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
USe Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@silverstream.my)
 * @version 1.0
 */
class announcementhandler extends CompositeHandler {
	function __construct(App $app) {
		parent::__construct('/root/system', 'announcement');
		// $currentStore = $app->pricevalidationFactory();
		// $this->addChild(new ext6gridhandler($this, $currentStore));

        $this->mapActionToRights('list', 'list');
        
        $this->mapActionToRights('fillform', 'add');
		$this->mapActionToRights('fillform', 'edit');

		$this->mapActionToRights('viewpicture', '/all/access');
		$this->mapActionToRights('detailview', 'list');
        $this->mapActionToRights('getRecordDisplay', '/all/access');
        $this->mapActionToRights('getSliders', '/all/access');
		$this->mapActionToRights('getSlidersMobile', '/all/access');
        
		$this->app = $app;

		$announcementStore = $app->announcementFactory();
		$this->addChild(new ext6gridhandler($this, $announcementStore, 1 ));
    }

		/**
	* function to populate selection data into form
	**/
	function fillform( $app, $params) {

        $announcementtype = \Snap\object\Announcement::getType();
        
        
		$object = $app->announcementFactory()->getById($params['id']);

	
        /*
		$relationship = \Snap\object\patient::getRelationship();
		$gender = \Snap\object\patient::getGender();
		$marital = \Snap\object\patient::getMarital();
		$ethnic = \Snap\object\patient::getEthnic();
		$smoke = \Snap\object\patient::getSmoke();
		$deadcause = \Snap\object\patient::getDeadCause();
		$deadloc = \Snap\object\patient::getDeadLocation();
		$patienttype = \Snap\object\patient::getType();
        */
		$cardiodoc = array();
		$cardiodoc[] = array("id" => 0, "name" => "Not required");
        //$rbac = new Rbac();
        /*
		$staffs = $app->userFactory()->searchTable()->select()->execute();
		foreach($staffs as $staff) {
			$roleArr = explode(";", $staff->role);
			if(count($roleArr) > 0) {
				foreach ($roleArr as $role) {
					$roleTitle = $rbac->Roles->getTitle($role);
					if($roleTitle == "Cardiologist") {
						$cardiodoc[] = array("id" => $staff->id, "name" => $staff->name);
					}
				}
			}
		}*/

        $picture = "";
		if($params['id']) {
			foreach($object->getAttachments() as $attachment) {
				$picture = '<img src="index.php?hdl=announcement&action=viewpicture&pid='.$params['id'].'&aid='.$attachment->id.'" height="280px">';
			}
		}

		echo json_encode([
			'success' => true,
			'pricecharges' => $pricecharges,
			'relationship' => $relationship,
			'gender' => $gender,
			'nokgender' => $gender,
			'marital' => $marital,
			'ethnic' => $ethnic,
			'smoke' => $smoke,
			'picture' => $picture,
			'deadcause' => $deadcause,
			'deadloc' => $deadloc,
			'patienttype' => $patienttype,
			'cardiodoc' => $cardiodoc,
            'filetype' => $filetype,
            'type' => $announcementtype,
		]);
	}

	/**
	* function to massage data before add/update
	**/
	function onPreAddEditCallback($object, $params) {
        //print_r($params);
		if(!isset($params['branchid']) || $params['branchid'] == 0 || $params['branchid'] == "") $object->branchid = $this->app->getUserSession()->getUser()->branchid;

		// $object->treatmenttype = \Snap\object\patient::getTreatmentTypeCode($params['treatmenttype']);
		// $object->meachinetreat = \Snap\object\patient::getMeachineType($params['meachinetreat']);
		$object->birthdayon = date("Y-m-d 00:00:00", strtotime($params['birthdayon']));
		if($params['deadon'] != NULL) $object->deadon = date("Y-m-d 00:00:00", strtotime($params['deadon']));
        
		if($params['id'] == '') $object->status = 0;

        /*
		$return = \Snap\object\patient::cleanNIRC($params['nric']);
		if($return !== FALSE) $object->nric = $return;

		$return = \Snap\object\patient::cleanNIRC($params['noknric']);
		if($return !== FALSE) $object->noknric = $return;
        */
		$type = \Snap\object\announcement::getType();
		if(isset($params['push'])) $object->type = $type[0]->id;
		else if(isset($params['announcement'])) $object->type = $type[1]->id;

		/* get patient picture */
		if(isset($_FILES['picture']) && !empty($_FILES['picture']['name'])) {

			/* resize image */
		    $info = getimagesize($_FILES["picture"]["tmp_name"]);
		    $mime = $info['mime'];
		    $targetFile = $_FILES["picture"]["tmp_name"]."-new";
		    $newWidth = "1200";

		    switch ($mime) {
	            case 'image/jpeg':
                    $image_create_func = 'imagecreatefromjpeg';
                    $image_save_func = 'imagejpeg';
                    $new_image_ext = 'jpg';
                    $quality = 30000;
                    break;

	            case 'image/png':
                    $image_create_func = 'imagecreatefrompng';
                    // $image_save_func = 'imagepng';
                    $image_save_func = 'imagejpeg';
                    $new_image_ext = 'png';
                    // $quality = 9;
                    $quality = 3000;
                    break;

	            case 'image/gif':
                    $image_create_func = 'imagecreatefromgif';
                    $image_save_func = 'imagegif';
                    $new_image_ext = 'gif';
                    $quality = 300000;
                    break;

	            default:
                    throw new Exception('Unknown image type.');
		    }

		    $img = $image_create_func($_FILES["picture"]["tmp_name"]);
		    list($width, $height) = getimagesize($_FILES["picture"]["tmp_name"]);

		    $newHeight = ($height / $width) * $newWidth;
		    $tmp = imagecreatetruecolor($newWidth, $newHeight);
		    imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

		    if (file_exists($targetFile)) unlink($targetFile);
		    $image_save_func($tmp, "$targetFile.$new_image_ext", $quality);

		    $_FILES['picture']['size'] = filesize("$targetFile.$new_image_ext");
			$str = file_get_contents("$targetFile.$new_image_ext");

			$array1 = $_FILES['picture'];
			$array2 = array('data' => $str);
			$newArray = $array1 + $array2;

			$attachmentlist['picture'] = $newArray;

			if($params['id'] > 0) {
				$existingAtt = $object->getAttachments();
				if(count($existingAtt) > 0) {
					foreach($existingAtt as $key => $value) {
						foreach($attachmentlist as $key1 => $attachment) {
							if($value->description == $key1) {
								$object->updateAttachment($value->id, $attachment);
							}
						}
					}
				}
				else {
					$object->picture = $object->addAttachment($attachmentlist);
				}
			}
			else if($object->isValid()) {
                //echo "hello";
				$object->picture = $object->addAttachment($attachmentlist);
			}

			unlink($_FILES["picture"]["tmp_name"]."-new");
		}



		return $object;
	}


	/**
	* function to update data after add/update
	**/
	function onPostAddEditCallback($savedRec, $params) {
        $id = $savedRec->id;
        //print_r($savedRec);
        //print_r($params);
        //$savedRec->resumeAddAttachment($id);
		if(isset($_FILES['picture']) && !empty($_FILES['picture']['name'])) $savedRec->updateAttachmentID($id);
	}


	/**
	* function to massage data before listing
	**/
	function onPreListing($objects, $params, $records) {
		foreach ($records as $key => $record) {
			// $records[$key]['treatmenttype'] = \Snap\object\patient::getTreatmentTypeName($record['treatmenttype']);
			$records[$key]['statusname'] = \Snap\object\announcement::getStatus($record['status']);
		}

		return $records;
	}

	/**
	* function to display image - this is from URL
	**/
	function viewpicture($app, $params) {
		$object = $app->announcementfactory()->getById($params['pid']);
		$object->onDownloadPictureAttachment($params['aid']);
	}


	/*
	* function to query based on permission
	*/
	function onPreQueryListing($params, $sqlHandle, $fields){
		//Filter list by branch
		//$branchId = $this->app->getUserSession()->getUser()->branchid;
		//$permission = $this->app->hasPermission('/root/hq/columns/show_all_branches');

		//if(!$permission){
		//	$sqlHandle->andWhere('branchid', $branchId);
		//}

		return array($params, $sqlHandle, $fields);
	}


	/**
	* function to provide detail properties
	**/
	function detailview($app, $params) {
        $object = $app->announcementfactory()->getById($params['id']);
        
        // Status
		if ($object->status == 0){
			$statusname = 'Inactive';
		}else if ($object->status == 1){
			$statusname = 'Active';
		}else {
			$vendorname = 'Unidentified';
		}

        if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';


		$detailRecord['default']['Code'] = $object->code;
	
		$detailRecord['default']['Title'] = $object->title;
		$detailRecord['default']['Description'] = $object->description;
		$detailRecord['default']['Rank'] = $object->rank;
        $detailRecord['default']['Display start on'] = $object->displaystarton->format('d-m-Y');
        $detailRecord['default']['Display end on'] = $object->displayendon->format('d-m-Y');
        $detailRecord['default']['Created on'] = $object->createdon->format('d-m-Y');
        $detailRecord['default']['Modified on'] = $object->modifiedon->format('d-m-Y');
        $detailRecord['default']['Created By'] = $createduser;
        $detailRecord['default']['Modified By'] = $modifieduser;

		$detailRecord['default']['Status'] = $statusname;


		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	/**
	* function to get patient summary to print on sticker
	**/
	function getRecordDisplay($app, $params) {
		    // Get Partner limits

			// Check if user is associated with partner
			$partnerid = $app->getUserSession()->getUser()->partnerid;

			if(0 != $partnerid){
		
				// Get All Announcements for Partner
				$announcements = $app->announcementFactory()->searchTable()->select()->where('partnerid', $partnerid)->execute();
			}else {
				// If user has no partner
				// Get All Announcements
				$announcements = $app->announcementFactory()->searchTable()->select()->execute();
			}

			
            $userId = $this->app->getUserSession()->getUser()->id;
            $product=$this->app->productStore()->getById(1); 
			
            
        // Get Display
            foreach($announcements as $announcement) {        
                $object = $app->announcementFactory()->getById($announcement->id);
                foreach($object->getAttachments() as $attachment) {
                    
                    // First check: Check for matching records
                    //if($object->picture == $attachment->id){    
                        
                        // Second check: Check if status is active
                        if($object->status == 1){
                                
                            // Third check: Check date range for availability
                                
                                $startdate = $object->displaystarton->format('Y-m-d H:i:s');
                                $enddate = $object->displayendon->format('Y-m-d H:i:s');

                                $currentDateTime = date('Y-m-d H:i:s');
                            

                                $displayDateBegin = date('Y-m-d H:i:s', strtotime($startdate)); 
                                $displayDateEnd = date('Y-m-d H:i:s', strtotime($enddate));

                                if($currentDateTime >= $displayDateBegin && $currentDateTime <= $displayDateEnd){

                                    $picture = '<img src="index.php?hdl=announcement&action=viewpicture&pid='.$announcement->id.'&aid='.$attachment->id.'" height="280px">';
                                    $imgSrc = "index.php?hdl=announcement&action=viewpicture&pid=$announcement->id&aid=$attachment->id";

                                    $pictures[]= array( 'id' => $attachment->id, 'picture' => $picture, 'filename' => $attachment->filename, 'type' => $attachment->sourceid, );
                                    $announce[]= array('id' => $announcement->id, 'title' =>  $announcement->title, 'imgSrc' =>  $imgSrc, 'picture' =>  $picture, 'rank' => $announcement->rank);

                                }
                        }
                        
                      

                      
                    //}
                }
                

               //$announcesaved[]= array('id' => $announcement->id, 'title' =>  $announcement->title, 'imgSrc' =>  $announcement->picture);
            }
            // Sort by rank
            usort($announce, function($a, $b) {
                return $a['rank'] <=> $b['rank'];
            });

            try{
                //print_r($announce);
                //print_r("no return");
				$return['success'] = true;
				$return['data'] = $announce;
                echo json_encode($return);
                //echo json_encode([ 'success' => true, 'userid' => $userId ,'attachment' =>$pictures , 'items' => $product, 'announcements' => $announce,  'data' => $userType]);
            }catch(\Exception $e){
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
            }
        
        
	}


	function getSliders($app, $params) {
		// Get Partner limits

		// Check if announcement has partner to begin with
		
		// Check if user is associated with partner
		$partnerid = $app->getUserSession()->getUser()->partnerid;
		if(0 != $partnerid){
			
			// Get All Announcements for Partner
			$announcements = $app->announcementFactory()->searchTable()->select()->where('partnerid', $partnerid)
				->andWhere('ismobile', 0)
				->andWhere('status', 1)
				->andWhere('displaystarton', '<=', date('Y-m-d H:i:s'))
				->andWhere('displayendon', '>=', date('Y-m-d H:i:s'))
				//->andWhere('partnerid', 45)
				->orderBy('rank', 'desc')
				->execute();
				
		}else {
			// If user has no partner
			// Get All Announcements
			$announcements = $app->announcementFactory()->searchTable()->select()	
				->where('ismobile', 0)	
				->andWhere('status', 1)
				->andWhere('displaystarton', '<=', date('Y-m-d H:i:s'))
				->andWhere('displayendon', '>=', date('Y-m-d H:i:s'))
				//->andWhere('partnerid', 45)
				->orderBy('rank', 'desc')
				->execute();
		}


		$html = '';
		$html .= '<div class="owl-carousel owl-theme">';
			
		foreach($announcements as $announcement) {        
			$object = $app->announcementFactory()->getById($announcement->id);
			foreach($object->getAttachments() as $attachment) {

				$html .= '<div class="item">';

				if ($announcement->title || $announcement->description){
					$html .= '<div class="slider-text-container">';
					if ($announcement->title){
						$html .= '<div class="slider-title">'.$announcement->title.'</div>';
					}
					if ($announcement->description){
						$html .= '<div class="slider-description">'.$announcement->description.'</div>';
					}
					$html .= '</div>';
				}

				$html .= '<img src="index.php?hdl=announcement&action=viewpicture&pid='.$announcement->id.'&aid='.$attachment->id.'">';
				$html .= '</div>';

				$pictures[]= array( 'id' => $attachment->id, 'picture' => $picture, 'filename' => $attachment->filename, 'type' => $attachment->sourceid, );
				$announce[]= array('id' => $announcement->id, 'title' =>  $announcement->title, 'imgSrc' =>  $imgSrc, 'picture' =>  $picture, 'rank' => $announcement->rank);

			}

		}
		$html .= '</div>';


		try{
			$return['success'] = true;
			$return['html'] = $html;
			echo json_encode($return);
			//echo json_encode([ 'success' => true, 'userid' => $userId ,'attachment' =>$pictures , 'items' => $product, 'announcements' => $announce,  'data' => $userType]);
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
        
        
	}

	function getSlidersMobile($app, $params) {
		// Get Partner limits

		// Check if announcement has partner to begin with
		
		// Check if user is associated with partner
		// $partnerid = $app->getUserSession()->getUser()->partnerid;
		// if(0 != $partnerid){
			
		// 	// Get All Announcements for Partner
		// 	$announcements = $app->announcementFactory()->searchTable()->select()->where('partnerid', $partnerid)
		// 		->andWhere('status', 1)
		// 		->andWhere('displaystarton', '<=', date('Y-m-d H:i:s'))
		// 		->andWhere('displayendon', '>=', date('Y-m-d H:i:s'))
		// 		//->andWhere('partnerid', 45)
		// 		->orderBy('rank', 'desc')
		// 		->execute();
				
		// }else {
		// 	// If user has no partner
		// 	// Get All Announcements
		// 	$announcements = $app->announcementFactory()->searchTable()->select()		
		// 		->where('status', 1)
		// 		->andWhere('displaystarton', '<=', date('Y-m-d H:i:s'))
		// 		->andWhere('displayendon', '>=', date('Y-m-d H:i:s'))
		// 		//->andWhere('partnerid', 45)
		// 		->orderBy('rank', 'desc')
		// 		->execute();
		// }
			
		// Get All Announcements for Partner
		$announcements = $app->announcementFactory()->searchTable()->select()->where('ismobile', 1)
		->andWhere('status', 1)
		->andWhere('displaystarton', '<=', date('Y-m-d H:i:s'))
		->andWhere('displayendon', '>=', date('Y-m-d H:i:s'))
		//->andWhere('partnerid', 45)
		->orderBy('rank', 'desc')
		->execute();

		foreach($announcements as $announcement) {        
			$object = $app->announcementFactory()->getById($announcement->id);
			foreach($object->getAttachments() as $attachment) {
                    
				// First check: Check for matching records
				//if($object->picture == $attachment->id){    
					
					// Second check: Check if status is active
					if($object->status == 1){
							
						// Third check: Check date range for availability
							
							$startdate = $object->displaystarton->format('Y-m-d H:i:s');
							$enddate = $object->displayendon->format('Y-m-d H:i:s');

							$currentDateTime = date('Y-m-d H:i:s');
						

							$displayDateBegin = date('Y-m-d H:i:s', strtotime($startdate)); 
							$displayDateEnd = date('Y-m-d H:i:s', strtotime($enddate));

							if($currentDateTime >= $displayDateBegin && $currentDateTime <= $displayDateEnd){

								$picture = '<img src="index.php?hdl=announcement&action=viewpicture&pid='.$announcement->id.'&aid='.$attachment->id.'" height="280px">';
								$imgSrc = "index.php?hdl=announcement&action=viewpicture&pid=$announcement->id&aid=$attachment->id";

								$pictures[]= array( 'id' => $attachment->id, 'picture' => $picture, 'filename' => $attachment->filename, 'type' => $attachment->sourceid, );
								$announce[]= array('id' => $announcement->id, 'title' =>  $announcement->title, 'imgSrc' =>  $imgSrc, 'picture' =>  $picture, 'rank' => $announcement->rank);

							}
					}
					
				  

				  
				//}
			}

		}


		try{
			$return['success'] = true;
			$return['data'] = $announce;
			// echo json_encode($return);
			echo json_encode([ 'success' => true, 'return' => $return]);
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
        
        
	}
}

?>
