<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyDocumentation;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class mydocumentationHandler extends CompositeHandler
{

    function __construct(App $app)
    {
        parent::__construct('/root/system', 'documentation');

        $this->mapActionToRights('list', 'list');
        $this->mapActionToRights('delete', 'add;edit');
        $this->mapActionToRights('fillform', 'add');
        $this->mapActionToRights('fillform', 'edit');
        $this->app = $app;        
        $this->addChild(new ext6gridhandler($this, $app->mydocumentationStore(), 1));
    }

    /**
     * function to populate selection data into form
     **/
    function fillform($app, $params)
    {
        if (0 < $params['id']) {
            /** @var MyDocumentation */
            $objects = $app->mydocumentationStore()->searchView(true, 2)->select()->where('id', $params['id'])->execute();

            $objFields =$objects[0]->getFields();
            $objViewFields =$objects[0]->getViewFields();
            $objFields = array_merge($objFields, $objViewFields);

            foreach ($objects as $oneObject) {
                $oneRecord = array();
                foreach ($objFields as $oneObjField) {
                    $data = $oneObject->{$oneObjField};
                    if ($oneObject->{$oneObjField} instanceof \DateTime) $data = $oneObject->{$oneObjField}->format("c");
                    $oneRecord[$oneObjField] = $data;
                }
                $records[] = $oneRecord;
            }
        }

        echo json_encode([
            'success' => true,
            'translations' => $records,
        ]);
    }

    /**
     * Function to masssage data before listing
     *
     * @param  MyDocumentation[] $objects
     * @param  array $params
     * @param  array $records
     * @return void
     */
    function onPreListing($objects, $params, $records)
    {
        $localizedObjects = [];

        foreach ($objects as $object) {
            $localizedObjects[$object->id] = $object;
        }

        array_walk($records, function (&$record, $key) use ($localizedObjects) {
            $localizedObject = $localizedObjects[$record['id']];
            $record['locales'] = implode(', ', $localizedObject->getAvailableLanguages());
        });

        return $records;
    }

    function doAction($app, $action, $params)
    {

        if ('add' == $action) {
            $params['status'] = 1;
        }

        parent::doAction($app, $action, $params);
    }

    /**
     * Function to update data after add/update
     *
     * @param  MyDocumentation $savedRec
     * @param  array $params
     * @return MyDocumentation
     **/
    function onPostAddEditCallback($savedRec, $params)
    {
        if (isset($params['myDocumentationTranslationParams']) && !empty($params['myDocumentationTranslationParams'])) {
            $translations = json_decode($params['myDocumentationTranslationParams']);
            $languages = [];

            // Save content for each language
            foreach ($translations as $translation) {
                if  (!$translation->filename || !$translation->filecontent || !$translation->language) {
                    continue;
                }

                // To check if valid base64
                $content = file_get_contents($translation->filecontent);

                // Save as string in JSON column, else cannot
                if (false !== $content) {
                    $savedRec->language    = $translation->language;
                    $savedRec->filecontent = $translation->filecontent;
                    $savedRec->filename    = $translation->filename;
                    $languages[]           = $translation->language;
                    $this->app->mydocumentationStore()->save($savedRec);
                } else {
                    throw new \Snap\InputException(gettext("Content of uploaded file is not valid"), \Snap\InputException::FORMAT_ERROR, null);
                }
            }

            $savedRec->syncContent($languages);

        }

        return $savedRec;
    }
}
