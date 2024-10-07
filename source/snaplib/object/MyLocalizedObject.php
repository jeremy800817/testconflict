<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\IEntity;
use Snap\ILocalizable;
use Snap\InputException;

/**
 * This abstract class 
 * represents an object that contains localized content.
 * 
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 01-Oct-2020
 * 
 * @property  $language         The language to save the contents on this object in
 */
abstract class MyLocalizedObject extends SnapObject implements ILocalizable
{
    private const CONTENT_STORE = 'mylocalizedcontent';
    /**
     * The members which are contains localized content
     * @var array 
     */
    protected $localizableMembers = array();

    /**
     * The MyLocalizedContent object for the current object
     * 
     * @var MyLocalizedContent
     */
    protected $localizedContent = null;

    /**
     * Array of LocalizedContents for caching purpose
     * 
     * @var array
     */
    private $contents = array();

    
    /**
     * Language of this object
     */
    private $language = null;

    /**
     * Returns the type of content
     * 
     * @return string The type of content to be returned ( See MyLocalizedContent object)
     */
    abstract protected function getContentType();


    /**
     * Returns the localizable fields that are available for this object
     * 
     */
    public function getLocalizableFields() {
        return array_keys($this->localizableMembers);
    }

    /**
     * Returns the localizedcontent object
     * 
     * @return MyLocalizedContent
     */
    public function getLocalizedContent()
    {
        return $this->localizedContent;
    }

    protected function populate($properties)
    {
        parent::populate($properties);

        foreach ($properties as $key => $prop) {
            if (array_key_exists($key, $this->localizableMembers) || "language" == $key) {
                $this->__set($key, $properties[$key]);
            }
        }
    }

    public function __get($nm)
    {
        if (! array_key_exists($nm, $this->localizableMembers)) {
            return parent::__get($nm);
        }

        if ("language" === $nm) {
            return $this->language;
        }

        return $this->localizableMembers[$nm];
    }

    public function __set($nm, $val)
    {
        parent::__set($nm, $val);
        if (!$this->isLockedFromEdit()) {

            if (array_key_exists($nm, $this->localizableMembers)) {
                $this->localizableMembers[$nm] = $val;
            } else if ("language" === $nm && $this->language != $val) {
                // Switching to a different language
                $this->getContentIn($val);
            } 

        }

        return $this;
    }

    /**
     * Returns an array which contains the currently populated data
     *
     * @return array
     */
    protected function getLocalizableData()
    {
        // Gather all the localizable fields
        $data = [];
        foreach ($this->getLocalizableFields() as $field) {
            $data[$field] = $this->localizableMembers[$field];
        }

        return $data;
    }

    /**
     * Returns an array of available localized contents
     * 
     * @return MyLocalizedContent[]
     */
    public function getAvailableContents()
    {
        $this->contents =  $this->getStore()->getRelatedStore(self::CONTENT_STORE)
                    ->searchTable()->select()
                    ->where('sourceid', $this->members['id'])
                    ->andWhere('sourcetype', $this->getContentType())
                    ->andWhere('status', MyLocalizedContent::STATUS_ACTIVE)
                    ->execute();
        return $this->contents;
    }

    /**
     * Returns an array of available languages 
     * 
     * @return array
     */
    public function getAvailableLanguages()
    {
        $langs = [];
        $contents = $this->getAvailableContents();
        foreach ($contents as $content) {
            $langs[] = $content->language;
        }

        return $langs;
    }

    /**
     * Return a list of available languages this content is localized for
     * 
     * @param string      $language       The target language (See MyLocalizedContent for list of enums)
     * 
     * @return boolean  Indicates if content was populated or not
     */
    public function getContentIn($language, $useCache = true)
    {
        if (! $language) {
            throw new InputException("Attempted to get invalid language ($language)", InputException::GENERAL_ERROR);
        }
        $this->language = $language;

        // Store the current content in cache
        if ($this->localizedContent) {
            $this->saveContentIn($this->localizedContent->language);
            $this->localizedContent = null;
        }

        if (null != $this->contents[$language] && $useCache) {
            // Get from cache if previously loaded
            $localizedContent = $this->contents[$language];
        } else if (!empty($this->members['id'])) {
            // Get from database
            $localizedContent = $this->getStore()->getRelatedStore(self::CONTENT_STORE)
                                            ->searchTable()->select()
                                            ->where('sourceid', $this->members['id'])
                                            ->andWhere('sourcetype', $this->getContentType())
                                            ->andWhere('language', $language)
                                            ->one();
            $this->contents[$language] = $localizedContent;
        }
        $this->localizedContent = $localizedContent;
        
        if ($this->localizedContent) {
            $this->decodeContent($this->localizedContent);
        }

        return $localizedContent != null;
    }


    /**
     * Sets the data members of the content object
     * 
     * @param  string   $language       The language to save the child object in
     * 
     * @return void
     */
    public function saveContentIn(string $language, bool $saveContentImmediately = false)
    {
        // Create the MyLocalizedContent object if not present in this object yet
        $contentStore = $this->getStore()->getRelatedStore(self::CONTENT_STORE);

        $content = $this->localizedContent;
        if (!$content && $this->localizedMembersNotEmpty()) {
            $content = $contentStore->create([
                'type'  => $this->getContentType(),
                'language'  => $language,
                'status'    => MyLocalizedContent::STATUS_ACTIVE,
            ]);
        }

        // Check to make sure the content is saved for the correct language
        if ($content) {
            if ($content->language != $language) {
                throw new InputException("The language of the content to be saved is different from the existing content", InputException::FIELD_ERROR, '');
            }

            // Apply the data inside the object
            $content->data = $this->encodeContent();
            $content->status = MyLocalizedContent::STATUS_ACTIVE;
            if ($saveContentImmediately && !empty($this->members['id'])) {
                $content = $contentStore->save($content);
            }
            $this->localizedContent = $content;
            $this->contents[$language] = $content;
        }
    }

    /**
     * Delete the content for a language
     * @param   string      $language       The language to delete in
     * 
     * @return  boolean
     */
    public function deleteContent(string $language)
    {
        // Get all available contents
        $contents = $this->getAvailableContents();

        // Find language in local cache
        $foundIdx = -1;
        foreach ($contents as $idx => $content) {
            if ($content->language == $language) {
                $foundIdx = $idx;
                break;
            }
        }

        // Found the content 
        if (-1 != $foundIdx) {
            $contentStore = $this->getStore()->getRelatedStore(self::CONTENT_STORE);

            if ($contentStore->delete($this->contents[$foundIdx])) {
                unset($this->contents[$foundIdx]);
                return true;
            }
        }

        return false;
    }

    /**
     * This method is called prior to the data being updated.  This method is intended to be used by
     * the object to make any prior actions before being updated
     *
     * @return Boolean   True if update can continue.  False otherwise.
     */
    public function onPrepareUpdate()
    {
        // $this->validateRequiredField($this->localizableMembers['language'], 'language');
        if ($this->localizedMembersNotEmpty() && !strlen($this->language)) {
            throw new InputException("language field is not set for this ".get_class($this)." object.", InputException::FIELD_ERROR, 'language');
        }

        if ($this->language) {
            $this->saveContentIn($this->language);
        }

        return true;
    }

    /**
     * This method is used to inform the object that the update has been completed.  The object can
     * perform any further post update actions as required.
     *
     * @param  IEntity $latestCopy The last copy of the object
     * @return bool    Indicates if post update is successful or not
     */
    public function onCompletedUpdate(IEntity $latestCopy)
    {
        parent::onCompletedUpdate($latestCopy);
        // Save the associated localized content if present.
        if ($this->localizedContent instanceof MyLocalizedContent) {
            $this->localizedContent->sourceid = $latestCopy->id;
            $this->localizedContent->sourcetype = $this->getContentType();
            $this->localizedContent->data = $this->encodeContent();
            $this->localizedContent->status = MyLocalizedContent::STATUS_ACTIVE;

            $contentStore = $this->getStore()->getRelatedStore(self::CONTENT_STORE);
            $this->localizedContent = $contentStore->save($this->localizedContent);
            return $this->localizedContent != null;
        }
        
    }

    /**
     * Remove all languages not specified in the param
     *
     * @param array $languages
     * @return MyLocalizedContent[]
     */
    public function syncContent($languages = [])
    {
        $contentStore = $this->getStore()->getRelatedStore(self::CONTENT_STORE);
        $availableContents = $this->getAvailableContents();

        foreach ($availableContents as $index => $content) {
            if (!in_array($content->language, $languages)) {
                $contentStore->delete($content);
                unset($availableContents[$index]);
            }
        }

        return $availableContents;
    }

    /**
     * Implementation method to encode localized content
     * 
     * @return string JSON encoded data of view members
     */
    function encodeContent()
    {
        return json_encode($this->getLocalizableData());
    }

    /**
     * Implementation method to decode content in the localizedcontent object
     * 
     * @return void
     */
    function decodeContent(MyLocalizedContent $content)
    {
        $decoded = json_decode($content->data, true); 

        foreach ($this->getLocalizableFields() as $field) {
            $this->localizableMembers[$field] = $decoded[$field];
        }
    }

    /**
     * 
     */
    private function localizedMembersNotEmpty()
    {
        foreach ($this->localizableMembers as $key => $val) {
            if ($val) {
                return true;
            }
        }
        return false;
    }

    protected function reset()
    {
        $this->language = null;
    }
    
}

?>