<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class myscreeninglistimportHandler extends CompositeHandler {

    function __construct(App $app) {
        parent::__construct('/root/system', 'amla');

        $this->mapActionToRights('list', 'list');
        $this->mapActionToRights('fillform', 'list');
        $this->mapActionToRights('getUrlForAmla', 'import');

        $this->app = $app;

        $myScreeningListImportLogStore = $app->myscreeninglistimportlogStore();
        $this->addChild(new ext6gridhandler($this, $myScreeningListImportLogStore, 1));
    }

    function onPreListing($objects, $params, $records)
    {
        foreach ($records as $key => $record) {
            if ('0000-00-00 00:00:00' == $records[$key]['importedon']) {
                $records[$key]['importedon'] = null;
            }
        }
        return $records;
    }

    function fillform( $app, $params) {
        
        $sourceType = \Snap\object\MyScreeningList::getSourceType();
      
        echo json_encode([ 'success' => true, 'sourcetype' => $sourceType ]);
    }
    
    /**
     * This method will determine is this particular handler is able to handle the action given.
     *
     * @param  App    $app    The application object (for getting user session etc to test?)
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */

    public function getUrlForAmla($app,$params)
    {           
        try{
             
            $myGtpScreeningManager = $this->app->mygtpscreeningManager();

            // Check source and execute import based on source
            if ('MOHA' == $params['sourcetype']) {
                $myGtpScreeningManager->importMohaList($params['url']);
            } else if ('UN' == $params['sourcetype']) {
                $myGtpScreeningManager->importUnList($params['url']);
            } else if ('BNM' == $params['sourcetype']) {
                $myGtpScreeningManager->importBnmList($params['url']);
            }

            $app->startCLIJob("AmlaReverificationJob.php", []);

            $now =  new \DateTime('now', $app->getUserTimezone());
            $userId = $this->app->getUserSession()->getUser()->id;

            $createScreeningListLog = $this->app->myscreeninglistimportlogStore()->create([
                "sourcetype" => $params['sourcetype'],
                "url" => $params['url'],
                "importedon" => $now,
                "importedby" => $userId,
                "status" => 1,
            ]);
            $this->app->myscreeninglistimportlogStore()->save($createScreeningListLog);

            echo json_encode(['success' => true, ]);      
        }catch(\Exception $e){
            echo json_encode(['success' => false, 'errorMessage' => 'The formatted link that you have just uploaded was change by its source, kindly email the link to IT support for onward action.']);                   
        }

    }
}
