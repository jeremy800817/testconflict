<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2023
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use Snap\InputException;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Jeremy <jeremy@silverstream.my>
 * @version 1.0
 */
class otcext6gridhandler extends ext6gridhandler {
	
    public function doAction( $app, $action, $params) {
        $otcUserActivityLog = ('true' == $params['otcuseractivitylog']) ? true : false;
        if ('list' == $action && $otcUserActivityLog) {
            parent::doAction($app, $action, $params);
            $this->doViewLog($app, $action, $params);
        } elseif ('add' == $action) {
            $this->doAddLog($app, $action, $params);
        } elseif ('edit' == $action) {
            parent::doAction($app, $action, $params);
            $this->doEditLog($app, $action, $params);
        } elseif ('search' == $action) {
            return $this->doSearchLog($app, $action, $params);
        }
	}
    
    public function doViewLog ($app, $action, $params) {
        if ($params['filter']) {
            $filters = json_decode($params['filter']);
            if (count($filters) > 0) {
                $temp = array();
                foreach ($filters as $filter) {
                    array_push($temp, $filter->property . ' = ' . $filter->value);
                }
                $activity = 'View record with filters [' . implode(",", $temp) . ']';
            }
        } else {
            $activity = 'View record';
        }
        
        $app->auditManager()->saveOtcUserActivityLog($params['hdl'], $action, $activity);
    }
    
    public function doAddLog ($app, $action, $params) {
        $result = array();
        
        ob_start();
        parent::doAddEdit($params);
        $result = json_decode(ob_get_contents(), true);
        ob_end_clean();
        
        echo json_encode($result);
        
        $activity = 'Add record with id: ' . $result['id'];
        $app->auditManager()->saveOtcUserActivityLog($params['hdl'], $action, $activity);
    }
    
    public function doEditLog ($app, $action, $params) {
        $activity = 'Update record with id: ' . $params['id'];
        $app->auditManager()->saveOtcUserActivityLog($params['hdl'], $action, $activity);
    }
    
    public function doSearchLog ($app, $action, $params) {
        $result = array();
        if ($params['filter']) {
            $filters = json_decode($params['filter']);
            if (count($filters) > 0) {
                ob_start();
                parent::doListing($params);
                $result = json_decode(ob_get_contents(), true);
                ob_end_clean();
            
                $temp = array();
                foreach ($filters as $filter) {
                    array_push($temp, $filter->property . ' = ' . $filter->value);
                }
                $activity = 'Search record with filters [' . implode(",", $temp) . ']';
                $app->auditManager()->saveOtcUserActivityLog($params['hdl'], $action, $activity);
            }
        }
        
        return $result;
    }
}