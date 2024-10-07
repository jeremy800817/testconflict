<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

Use Snap\IEntity;

/**
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name                 Type                Description
 * @property-read       int                 $id                 ID of the system
 * @property            string              $code               The code
 * @property            string              $title              Title of the announcement
 * @property            string              $description        Description of the announcement
 * @property            string              $content            Content of the announcement
 * @property            string              $contentrepo        Directory for the content of the announcement
 * @property            int                 $rank               Position for the content of the announcement
 * @property            enum                $type               The type of announcement
 * @property            int                 $status             The status for this announcement.  (Active / Inactive)
 * @property            DateTime            $displaystarton     Time this announcement start displaying
 * @property            DateTime            $displayendon       Time this announcement end displaying
 * @property            DateTime            $createdon          Time this record is created
 * @property            DateTime            $modifiedon         Time this record is last modified
 * @property            int                 $createdby          User ID
 * @property            int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class Announcement extends SnapObject
{

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_QUEUED = 2;        // Queued to be pushed. Only valid for push announcements

    const TYPE_PUSH = 'PUSH';
    const TYPE_ANNOUNCEMENT = 'ANNOUNCEMENT';

    /**
     * This method will initialise the array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's constructor.
     *
     * @return void
     */
    protected function reset()
    {
        $this->members = array(
            'id' => null,
			'partnerid' => null,
            'code' => null,
            'title' => null,
            'description' => null,
            'content' => null,
            'picture' => null,
            'rank' => null,
            'type' => null,
            'status' => null,
            'displaystarton' => null,
            'displayendon' => null,
			'ismobile' => null,
			'timer' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->localizableMembers = array(
            'title'     => null,
            'content'   => null
        );
    }

    protected function getContentType()
    {
        return LocalizedContent::TYPE_ANNOUNCEMENT;
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid()
    {
        /*
        $this->validateRequiredField($this->members['type'], 'type');
        $this->validateRequiredField($this->members['code'], 'code');
        $this->validateRequiredField($this->members['rank'], 'rank');
        $this->validateRequiredField($this->members['displaystarton'], 'displaystarton');
        $this->validateRequiredField($this->members['displayendon'], 'displayendon');
        $this->validateRequiredField($this->localizableMembers['title'], 'title');
        $this->validateRequiredField($this->localizableMembers['content'], 'content');
        */
        return true;
    }

    
	/*
        This method to get the listing of Announcement Type
    */
	public function getType() {

		$categoryArr = [];
		$lists = [
			self::TYPE_PUSH ,
			self::TYPE_ANNOUNCEMENT,
		];

		foreach ($lists as $key => $value) {
			$categoryArr[] = (object)array("id" => $value, "code" => ucfirst(strtolower($value)));
		}

		return $categoryArr;
    }

    
	/**
	 * This method is called before a delete operation is done.
	 *
	 * @return Boolean  True if can continune to delete the object.  False otherwise.
	 */
	public function onPredelete() {
		$attachmentStore = $this->getStore()->getRelatedStore('attachment');

  		if(!$this->attachments) $this->getAttachments();
	 	foreach($this->attachments as $aAttachment) {
			$attachmentStore->delete($aAttachment);
		}
		return parent::onPredelete();
	}


	/**
	 * This method is used to inform the object that the update has been completed.  The object can
	 * perform any further post update actions as required.
	 *
	 * @param  IEntity $latestCopy The last copy of the object
	 * @return None
	 */
	public function onCompletedUpdate(IEntity $latestCopy) {
		//Save those shifts objects added but was not saved because our branch have no id yet.
		if(0 == $this->members['id'] && count($this->attachments)) {
			foreach($this->attachments as $aAttachment) {
				$aAttachment->sourceid = $latestCopy->id;
				$this->getStore()->getRelatedStore('attachment')->save($aAttachment);
			}
		}
		return parent::onCompletedUpdate($latestCopy);
	}


    
    /**
	 * This method to get picture for banner
	 * @param from onPreAddEditCallback(), onPreListing()
	 * @throws InputException
	 */
	public function getAttachments() {
		if(0 != $this->members['id'] && $this->members['picture'] > 0 && 0 == count($this->attachments)) {
			$selectHandle = $this->getStore()->getRelatedStore('attachment')->searchTable()->select()
											 ->where('sourceid', $this->members['id']);
            $attachments = $selectHandle->execute();
          
        }
        
		return $attachments;
    }
    
    
	public function getStatus($code) {
		if($code == self::STATUS_INACTIVE) $status = "0";
		elseif($code == self::STATUS_INACTIVE) $status = "1";
		elseif($code == self::STATUS_QUEUED) $status = "2";
		return $status;
	}

    
	/**
	 * add picture to attachment table
	 *
	 * @param variable  $attachment array[] array from onPreAddEditCallback()
	 */
	public function addAttachment($attachment) {
        //echo "add: ".$this->members['id'];
		foreach ($attachment as $key => $aAttachment) {
            
			$newAttachment = $this->getStore()->getRelatedStore('attachment')->create([
				'sourcetype' => 'ANNOUNCEMENT', 'sourceid' => $this->members['id'],
				'description' => $key, 'filename' => $aAttachment['name'],
				'filesize' => $aAttachment['size'], 'mimetype' => $aAttachment['type'], 'data' => $aAttachment['data']
            ]);
            /*$newAttachment = $this->app->attachmentStore()->create([
                'sourcetype' => 'ANNOUNCEMENT', 'sourceid' => $this->members['id'],
                'description' => $key, 'filename' => $aAttachment['name'],
                'filesize' =>  $aAttachment['size'], 'mimetype' => $aAttachment['type'], 'data' => $aAttachment['data'] 
            ]);*/


			$newAttachment = $this->getStore()->getRelatedStore('attachment')->save($newAttachment);
			$this->attachments[] = $newAttachment;
		}
		return true;
	}

    
	/**
	 * This method will update the attachment object related to this patient
	 * @param $attachmentid  attachment id
	 * @param $updateAttachment from onPreAddEditCallback()
	 * @throws InputException
	 */
	public function updateAttachment($attachmentid, $updateAttachment) {
		$updateAtt = $this->getStore()->getRelatedStore('attachment')->getById($attachmentid);

		$updateAtt->filename = $updateAttachment['name'];
		$updateAtt->filesize = $updateAttachment['size'];
		$updateAtt->mimetype = $updateAttachment['type'];
		$updateAtt->data =  $updateAttachment['data'];

		$this->getStore()->getRelatedStore('attachment')->save($updateAtt);
		return true;
	}


	/**
	 * This method will update patient row for picture after attachment save
	 * @param $id from onPostAddEditCallback()
	 * @throws InputException
	 */
	public function updateAttachmentID($id){
        //echo "update: ".$id;
        $allattachment = $this->getStore()->getRelatedStore('attachment')->searchTable()->select()->where('sourceid', 0)->execute();
        //print_r("wallaaahaotest");
        $record = $this->getStore()->getById($id);
		foreach($allattachment as $key => $attachment){
            

            
            // $updateAtt = $this->getStore()->getRelatedStore('attachment')->getById($attachment->id);
            // $updateAtt->sourceid = $id;
            // $this->getStore()->getRelatedStore('attachment')->save($updateAtt);
            
            // save picture id from attachment id
            $record->picture = $attachment->id;
            $savedRec = $this->getStore()->save($record);
           
            // update sourceid = 0 to sourceid from announcement id
            //$attachment->sourceid = $savedRec->id;
            
           $attc = $attachment;
            //$saveApp = $this->getStore()->getRelatedStore('attachment')->save($attachment);
            //$this->getStore()->getRelatedStore('attachment')->save($attachment);
        }

        $attc->sourceid = $id;
           
        $this->getStore()->getRelatedStore('attachment')->save($attc);
       
        //$attachment->sourceid = $savedRec->id;
           
        //$saveApp = $this->getStore()->getRelatedStore('attachment')->save($attachment);
        

	}


	/**
	 * This method is used to download picture in form. Call by viewpicture
	 *
	 * @param $id get picture id from url
	 * @return
	 */
	public function onDownloadPictureAttachment( $id) {
		$attRecord = $this->getStore()->getRelatedStore('attachment')->getById($id);

		if (null != $attRecord->filename) {
			if (strlen($attRecord->mimetype) == 0) $mimetype = 'text/plain';
			else $mimetype = $attRecord->mimetype;
			header('Content-Type: '.$mimetype);
			header('Content-Length: '.$attRecord->filesize);
			header('Content-Disposition: inline; filename=\'$attRecord->filename\'');
			header('Content-Transfer-Encoding: binary');
			echo $attRecord->data;
			exit;
		}
		return FALSE;
	}



}
