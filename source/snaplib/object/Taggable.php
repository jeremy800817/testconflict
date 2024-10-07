<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;

Use Snap\IEntity;
USe Snap\IEntityStore;
Use Snap\ICacheable;
Use Snap\InputException;

/**
 * This represents the main interface class to a record in storage.
 *
 *
 * @author   Devon Koh <devon@silverstream.my>
 * @version  1.0
 * @package  data object
 * @abstract
 */
abstract class taggable extends SnapObject {
	/**
	 * Temporary store for taglink object
	 * @var TagLink
	 */
	protected $taglink = null;

	protected $taglinkToRemove = array();

	/**
	 * The source type to extract taglink
	 * @var null
	 */
	protected $tagSourceType = null;

	/**
	 * This method will create a linkage to the tag
	 * @param Tag $tag Tag to associate with
	 */
	public function addTag( Tag $tag) {
		$taglinkStore = $this->getStore()->getRelatedStore('taglink');
		if($this->members['id'] > 0 && !$this->taglink) $this->getTagLink();
		if(isset($this->taglink[$tag->id])) return;  //ignore
		$taglink = $taglinkStore->create([
			'tagid' => $tag->id, 'sourcetype' => $this->tagSourceType, 'sourceid' => $this->members['id'], 'status' => 1
			]);
		$this->taglink[$tag->id] = $taglink;
	}

	/**
	 * This method will add a tag to the type of assetcat object.
	 * @param  Tag    $tag The tag object
	 * @return None
	 */
	public function removeTag(Tag $tag) {	
		if($this->members['id'] > 0 && !$this->taglink) $this->getTagLink();
		if(is_array($this->taglink)) {
			$tmpArray = array();
			foreach($this->taglink as $aLink) {
				if($aLink->tagid == $tag->id) $this->taglinkToRemove[] = $aLink;
				else $tmpArray[] = $aLink;
			}
			$this->taglink = $tmpArray;
		}
	}

	/**
	 * Get all the taglink objects that belongs to this assetcat
	 * @return TagLink An array of the taglink objects
	 */
	public function getTagLink() {
		$copy = array();
		$taglinkStore = $this->getStore()->getRelatedStore('taglink');
			
		if(is_array($this->taglink)) {
			foreach( $this->taglink as $aTag) $copy[] = $aTag;
			return $copy;
		}
		$copy = $taglinkStore->searchView()
									->select()->where('sourcetype', $this->tagSourceType)
									->andWhere('sourceid', $this->members['id'])
									->execute();
		if($copy) {
			foreach($copy as $aTagLink) {
				$this->taglink[$aTagLink->tagid] = $aTagLink;
			}
		} else {
			$copy  = $this->taglink = array();
		}
		return $copy;
	}

	/**
	 * Check if the asset has tagcode linked to it.
	 * @param  String  $tagCode The tag code to check against
	 * @return boolean          True if the assetcat is linked with the tagcode.  False otherwise.
	 */
	public function isTagged( $tagCodeOrId) {
		if(!$this->taglink) $this->getTagLink();
		foreach($this->taglink as $aLink) {
			if($tagCodeOrId == $aLink->tagcode) return true;
			else if(is_object($tagCodeOrId) && $tagCodeOrId->id == $aLink->tagid) return true;
			else if(!is_object($tagCodeOrId) && intval($tagCodeOrId) > 0 && $tagCodeOrId == $aLink->tagid) return true;
		}
		return false;
	}

	/**
	 * This method is called before a delete operation is done.
	 * 
	 * @return Boolean  True if can continune to delete the object.  False otherwise.
	 */
	public function onPredelete() {
		$taglinkStore = $this->getStore()->getRelatedStore('taglink');
		if(!$this->taglink) $this->getTagLink();
		foreach($this->taglink as $aLink) {
			$taglinkStore->delete($aLink);
		}
		return true;
	}

	/**
	 * This method is used to inform the object that the update has been completed.  The object can 
	 * perform any further post update actions as required.
	 * 
	 * @param  IEntity $latestCopy The last copy of the object
	 * @return None
	 */
	public function onCompletedUpdate(IEntity $latestCopy) {
		$taglinkStore = $this->getStore()->getRelatedStore('taglink');
		parent::onCompletedUpdate($latestCopy);
		if($this->taglink) {
			foreach($this->taglink as $aLink) {				
				if(0 == $aLink->id) {
					$aLink->sourceid = $latestCopy->id;
					$taglinkStore->save($aLink);
				}
			}			
		}
		foreach($this->taglinkToRemove as $aLink) $taglinkStore->delete($aLink);
		return true;
	}

	/**
	 * This method will implement serializing the object into a cacheable string for optimum storage.
	 * @return String
	 */
	public function toCache() {
		$objectPartStr = parent::toCache();
		$this->taglink = null;
		$this->getTagLink();
		if(is_array($this->taglink)) {
			foreach($this->taglink as $aLink) {
				$objectPartStr .= "***" . $aLink->toCache();
			}
		}
		return $objectPartStr;
	}

	/**
	 * This method will need to implement expanding the object back to its original from the cached data provided.
	 * @param  string $data The original data provided in toCache()
	 * @return None
	 */
	public function fromCache( $data) {
		$taglinkStore = $this->getStore()->getRelatedStore('taglink');
		$partsArray = explode('***', $data);
		$this->taglink = array();
		for($i = 1; $i < count($partsArray); $i++) {
			$aLink = $taglinkStore->create();
			$aLink->fromCache(($partsArray[$i]));
			$this->taglink[] = $aLink;
		}
		return parent::fromCache($partsArray[0]);
	}
}
?>