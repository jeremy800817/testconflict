<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;

/**
 * Encapsulates the tag table on the database
 *
 * This class encapsulates the tag table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$ID 				ID of the system
 * @property       	int   				$type        	    type of logistic (redemption/replenishment)
 * @property       	int        			$typeid             type parent table ID (redemption_id/replenishment_id)
 * @property       	int     			$vendorid     	    assigned logistic vendor of the logistic (system refid) (courier) (ace logisic staff(s) as vendor in system)
 * @property       	int     			$senderid        	assigned sender ID of the logistic (system user) (ace logistic staff)
 * @property       	string    			$awbno     	        AWB number
 * @property       	string    			$contactname1     	contact name 1
 * @property        string              $contactname2     	contact name 2
 * @property       	string  			$contactno1    		contact number 1
 * @property       	string        		$contactno2    		contact number 2
 * @property       	string     			$address1    		Address LINE 1
 * @property       	string   			$address2    		Address LINE 2
 * @property       	string   			$address3    		Address LINE 3
 * @property       	string   			$city    		    City
 * @property       	string   			$postcode    		Postcode
 * @property       	string   			$country    		Country
 * @property       	int   			    $frombranchid    	logistic sent from Branch ID (ACE)
 * @property       	int   			    $tobranchid    	    logistic sent to Branch ID (OTHER)
 * @property       	DateTime   			$senton    	        logistic sent time
 * @property       	int        			$sentby    	        logistic sent by (system user)
 * @property       	string   			$recievedperson    	logistic recieved by person (name)
 * @property       	DateTime   			$deliveredon    	logistic delivered time
 * @property       	int        			$deliveredby    	logistic delivered by (system refid)
 * @property       	int       			$status    		    The status for this price validation.  (Active / Inactive)
 * @property       	DateTime   			$createdon     		Time this record is created
 * @property       	int        			$createdby     		User id
 * @property       	DateTime 	   		$modifiedon    		Time this record is last modified
 * @property       	int         		$modifiedby    		User id
 *
 * @version 1.0
 */
class Logistic extends SnapObject {
	const TYPE_REDEMPTION = 'Redemption';
    const TYPE_REPLENISHMENT = 'Replenishment';
    const TYPE_BUYBACK = 'Buyback';
    const TYPE_RTN_DAMAGE = 'Rtn_Damage';
    const TYPE_RTN_MISMATCH = 'Rtn_Mismatch';

    const STATUS_PENDING = 0; // init data -- && for redemption(appointment)
    const STATUS_PROCESSING = 1; // init data
    const STATUS_PACKING = 2; // private info, for packing records
    const STATUS_PACKED = 3; // private info, for packing records
	const STATUS_COLLECTED = 4;
	const STATUS_SENDING = 5;

    const STATUS_DELIVERED = 6;
	const STATUS_COMPLETED = 7;

    const STATUS_FAILED = 8;
    const STATUS_MISSING = 9;


    //const STATUS_COLLECTING = 10;

	// vendor means logistic vendor
	// id means logistic id
	// type means logistic type
	// etc
	// to get vendors id , id = tagStore[LogisticVendor]->id
    const VENDOR_ACEDELIVERY_VALUE = 'CourAce';
    const VENDOR_GDEX_VALUE = 'CourGDEX';
    const VENDOR_LINCLEAR_VALUE = 'CourLineClear';
	const VENDOR_JNT_VALUE = 'CourJ&T';

    const MAX_ATTEMPS = 3;

	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset() {
		$this->members = [
			'id' => null,
			'partnerid' => null,
			'type' => null,
			'typeid' => null,
			'partnerid' => null,
			'vendorid' => null,
			'senderid' => null,
			'vendorrefno' => null,
			'awbno' => null,
			'contactname1' => null,
            'contactname2' => null,
			'contactno1' => null,
			'contactno2' => null,
			'address1' => null,
			'address2' => null,
			'address3' => null,
			'city' => null,
			'postcode' => null,
			'state' => null,
			'country' => null,
			'frombranchid' => null,
			'tobranchid' => null,
			'senton' => null,
			'sentby' => null,
			'recievedperson' => null,
			'deliveredon' => null,
			'deliveredby' => null,
			'attemps' => null,
			'pickupdatetime' => null,
			'pickupref' => null,
			'modifiedstatusexport' => null,
			'deliverydate' => null,
			'status' => null,
			'createdon' => null,
			'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
		];

		$this->viewMembers = [
			'vendorname' => null,
            'sendername' => null,
            'frombranchname' => null,
            'tobranchname' => null,
            'sentbyname' => null,
            'deliveredbyname' => null,
            'vendorvalue' => null,
            'vendordescription' => null,
            'createdbyname' => null,
            'modifiedbyname' => null,

        ];
	}

	public function isValid(){
		return true;
	}


	/*

        This method to get the listing of Logistic's statusses

    */

    public function getBoStatus($user) {



        $typeArr = [];

        $statusname = "";



        $displayStatus = [

                self::STATUS_PENDING => array( 'forSalesman' => false, 'forOperator' => true, 'description' => gettext('Pending')), // Incoming request from MBB (eg: redemption)

                //self::STATUS_CONFIRMED, // Confirmed the receiving of the order

                self::STATUS_PROCESSING => array( 'forSalesman' => false, 'forOperator' => true, 'description' => gettext('Processing')), // Packing of order packages upon receiving delivery order from SAP

                //self::STATUS_PACKING, // Salesman/ Courier depart from warehouse

                self::STATUS_PACKED => array( 'forSalesman' => false, 'forOperator' => true, 'description' => gettext('Packed')), // Salesman/ Courier depart from warehouse


                self::STATUS_COLLECTED => array( 'forSalesman' => true, 'forOperator' => true, 'description' => gettext('Collected')), // Salesman/ Courier depart from warehouse

                //self::STATUS_COLLECTED, // Salesman collected package from warehouse

                self::STATUS_SENDING => array( 'forSalesman' => true, 'forOperator' => true, 'description' => gettext('In Transit')), // Salesman/ Courier depart from warehouse

                //self::STATUS_INTRANSIT, // Salesman/ Courier depart from warehouse

                self::STATUS_DELIVERED => array( 'forSalesman' => true, 'forOperator' => true, 'description' => gettext('Delivered')), // Package succesfully delivered

                self::STATUS_COMPLETED => array( 'forSalesman' => false, 'forOperator' => true, 'description' => gettext('Completed')), // Salesman/ Courier depart from warehouse

                self::STATUS_FAILED => array( 'forSalesman' => false, 'forOperator' => true, 'description' => gettext('Failed')), // Salesman/ Courier depart from warehouse

                self::STATUS_MISSING => array( 'forSalesman' => false, 'forOperator' => true, 'description' => gettext('Missing')), // Salesman/ Courier depart from warehouse

                //self::STATUS_CANCELLED, // Salesman/ Courier depart from warehouse

                //self::STATUS_COLLECTING, // Salesman/ Courier depart from warehouse

                //self::STATUS_COLLECTED, // Salesman/ Courier depart from warehouse

                //self::STATUS_COMPLETED, // Delivery completion approved by admin

                //self::STATUS_UNDELIVERED //

        ];

        //determine user type to show the appropriate statuses only

        $userCheckField = ($user->isSale()) ? 'forSalesman' : 'forOperator';

        foreach($displayStatus as $status => $data) {

            if( $data[$userCheckField]) {
				
                $typeArr[] = (object)array("id" => $status, "code" => $data['description']);

            }

        }

        return $typeArr;

    }

    public function getStatusText()
    {
        switch ($this->members['status']) {
                case Logistic::STATUS_PENDING:
                    $readable = gettext('Pending');
                    break;
                case Logistic::STATUS_PROCESSING:
                    $readable = gettext('Processing');
                    break;
                case Logistic::STATUS_PACKING:
                    $readable = gettext('Packing');
                    break;
                case Logistic::STATUS_PACKED:
                    $readable = gettext('Packed');
                    break;
                case Logistic::STATUS_COLLECTED:
                    $readable = gettext('Collected');
                    break;
                case Logistic::STATUS_SENDING:
                    $readable = gettext('In Transit');
                    break;
                case Logistic::STATUS_DELIVERED:
                    $readable = gettext('Delivered');
                    break;
                case Logistic::STATUS_COMPLETED:
                    $readable = gettext('Completed');
                    break;
                case Logistic::STATUS_FAILED:
                    $readable = gettext('Failed');
                    break;
                case Logistic::STATUS_MISSING:
                    $readable = gettext('Missing');
                    break;
                default:
                   $readable = gettext('unknown');
        }
        return $readable;
    }

}
