<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;
Use \Snap\IObservation;
// USe Snap\object\fees;
USe Snap\object\Order;

/**
 * Encapsulates the eventmessage table on the database
 *
 *  @property-read 	int         $id           Primary Key
 *  @property 		string      $code         Code for this message
 *  @property  		enum        $replace      Replacement regular expression
 *  @property  		double      $subject      Subject line
 *  @property 		int         $content      Email content
 *  @property 		\Datetime   $createdon    Time this record is created
 *  @property 		\Datetime   $modifiedon   Time this record is last modified
 *  @property 		int         $status       Status active(1), suspended(2)
 *  @property 		int         $createdby    User ID
 *  @property 		int         $modifiedby   User ID
 *
 *
 * @author Chuah
 * @version 1.0
 * @created 2017/8/3 5:16 PM
 * @package  snap.base
 */
class EventMessage extends SnapObject
{
    /**
     * Reset all values in $this->members array  this is where the object member
     * variables get initialized to its default values inherited class should
     * implement this abstract function
     * @abstract
     * @return	none
     */
    public function reset()
    {
        $this->members = array(
            'id' => null,
            'code' => null,
            'replace' => null,
            'subject' => null,
            'content' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
            'status' => null,
        );
        $this->viewMembers = array(
            
        );
    }

    /**
     * Check if all values in $this->members array is valid  this is where the object
     * member variables get validated for legal values inherited class should
     * implement this abstract function
     * @abstract
     * @return	true if all member data has valid values. Otherwise false.
     */
    public function isValid()
    {
        return true;
    }

    /**
     * This is the actual method to process the template and create a message based on the application context and
     * object state.
     *   ##methodName_(field)##, ##OTHERPARAM_fieldname##, objectfield,  CUSTOM_MESSAGE_(.*)
     *
     * @param  App                      $app        Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservation             $state      The observation object
     * 
     * @return Snap\object\eventlog                 Event log object
     */
    public function apply($app, EventTrigger $trigger, IObservation $state)
    {
        $target = $state->target;
        //Get the viewable object - default to the 1st view
        $viewableObj = $target->getStore()->getById($target->id, array(), false, 1);
        //IF there is a custom message that needs to be formatted in a special way, run it first.
        if (preg_match('/CUSTOM_MESSAGE_(.*)/', $this->content, $matches)) {
            $this->log("[NotificationManager]  Calling custom formatting function {$matches[1]} for this event.", SNAP_LOG_DEBUG);
            call_user_func_array(array($this, 'custommessage_'.$matches[1]), array($app, $state, $trigger, $viewableObj));
        }
        //For all standard messages to be formatted.  Event custom formatted messages can run through this filter again if needed.
        $replacementPattern = $replacementField = array();
        $replacementLines = explode(',', $this->replace);
        foreach ($replacementLines as $aLine) {
            $arr = explode('||', $aLine);
            $replacementPattern[] = '/' . $arr[0] . '/';
            if (preg_match('/^##OTHERPARAM_(.*)##$/', $arr[1], $matches)) {
                $val = $this->translate_OTHERPARAM($state, $matches[1]);
            } elseif (preg_match('/^##(.*)_\((.*)\)##$/', $arr[1], $matches)) {
                if (method_exists($this, 'translate_' . $matches[1])) {
                    $val = call_user_func_array(
                                array($this, 'translate_'.$matches[1]),
                                array($app, $trigger, $state, $viewableObj, $matches[2])
                    );
                } else {
                    $val = 'N/A';
                }
            } elseif (preg_match('/^##(.*)##$/', $arr[1], $matches)) {
                if (method_exists($this, 'translate_' . $matches[1])) {
                    $val = call_user_func_array(
                            array($this, 'translate_'.$matches[1]),
                            array($app, $trigger, $state, $viewableObj, null)
                    );
                } else {
                    $val = 'N/A';
                }
            } elseif (preg_match('/^##(.*)by##$/', $arr[0]) && intval($viewableObj->{$arr[1]}) > 0) {
                $val = $this->translate_username($app, $state, $viewableObj, $arr[1]);
            } else {
                $val = $viewableObj->{$arr[1]};
            }

            //Implement generic implementation
            if (is_numeric($val) && (preg_match('/amount/', $arr[0]) || preg_match('/trans/', $arr[0]))) {
                $val = number_format($val, 4);
            } elseif ($val instanceof \DateTime) {
                // $val = $val->format('g:ia \o\n l jS F Y');
                $val = $val->format('Y-m-d H:i:s'); 
            }
            $replacementField[] = $val;
        }

        $subject = preg_replace($replacementPattern, $replacementField, $this->subject);
        $content = preg_replace($replacementPattern, $replacementField, $this->content);
        // $content = $subject . "\n---\n" . $content;
        $content = $content;
        if ($state->otherParams) {
            if (array_key_exists('custom_subject', $state->otherParams)) {
                $subject = $state->otherParams['custom_subject'];
            }
            if (array_key_exists('custom_log', $state->otherParams)) {
                $content = $state->otherParams['custom_log'];
            }
        }

        // get subscribers
        $subscribers = $trigger->getEventSubscriber($app, $state);

        $eventLog = $app->eventLogStore()->create([
            'triggerid' => $trigger->id,
            'groupid' => $target->{$trigger->groupidfieldname},
            'objectid' => $target->id,
            'reference' => (0<$target->xrefno) ? $target->xrefno : ((0<$target->code) ? $target->code : $target->name),
            'subject' => $subject,
            'log' => $content,
            'sendto' => $subscribers->receiver,
            'sendon' => new \DateTime(),
            'status' => SnapObject::STATUS_ACTIVE,
        ]);
        return $eventLog;
    }

    /**
     * This is customised implementation to format a message.
     *
     * @param  App                   	$app       	Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string             		$fieldValue Value of data to do translation on
     * 
     * @return mixed                 	The translated string
     */
    private function translate_partner($app, EventTrigger $trigger, IObservation $state, $object, $fieldValue)
    {
        $partner = $app->getPartnerManager()->getPartner($state->target->{$trigger->groupidfieldname});
        $val = substr($eventTemplate['target'], 0, 1) . strtolower(substr($eventTemplate['target'], 1));
        return $val;
    }


    /**
     * This method translate the current user ID to a name
     *
     * @param  App                      $app        Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string                   $fieldValue Value of data to do translation on
     * 
     * @return string                   Name of the user
     */
    private function translate_currentuser($app, EventTrigger $trigger, IObservation $state, $object, $fieldValue)
    {
        $userObj = $app->getUserSession()->getUser();
        $val = $userObj->name;
        if ($fieldValue) {
            return $userObj->$fieldValue;
        }
        return $val;
    }

    /**
     * This is customised implementation to format a message.
     *
     * @param  App                   	$app       	Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string             		$fieldValue Value of data to do translation on
     * 
     * @return string                 	The username
     */
    private function translate_username($app, EventTrigger $trigger, IObservation $state, $object, $fieldValue)
    {
        $user = $app->userStore()->getById($object->{$fieldValue});
        return $user->name;
    }

    /**
     * This is customised implementation to format a message.
     *
     * @param  App                   	$app       	Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string             		$fieldValue Value of data to do translation on
     * @return mixed                 	The translated string
     */
    private function translate_OTHERPARAM(IObservation $state, $fieldValue)
    {
        return $state->otherParams[$fieldValue];
    }

    /**
     * Returns the account type used
     * @param  App                      $app        Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string                   $fieldValue Value of data to do translation on
     * 
     * @return string                   account type description
     */
    private function translate_ACCOUNTTYPE( $app, Snap\object\eventtrigger $trigger, IObservation $state, $object, $fieldValue) {
        $accountStore = $app->partnerStore()->getRelatedStore('account');
        $account = $accountStore->getById( $state->target->{$fieldValue});
        return $account->currencytype;
    }

    /**
     * Returns the account information
     * 
     * @param  App                      $app        Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string                   $fieldValue Value of data to do translation on
     * 
     * @return string                   account field data
     */
    private function translate_ACCOUNTINFO( $app, Snap\object\eventtrigger $trigger, IObservation $state, $object, $fieldValue) {
        $accountStore = $app->partnerStore()->getRelatedStore('account');
        list($accountid, $accountfield) = explode('.', $fieldValue);
        $account = $accountStore->getById( $state->target->{$accountid});
        return $account->{$accountfield};
    }

    /**
     * Returns fee type used
     * 
     * @param  App                      $app        Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string                   $fieldValue Value of data to do translation on
     * 
     * @return string                   fee type description
     */
    private function translate_FEETYPE( $app, Snap\object\eventtrigger $trigger, IObservation $state, $object, $fieldValue) {
        switch( $state->target->{$fieldValue}) {
            case fees::TYPE_FUNDIN: $val = gettext('Direct debit');
            break;
            case fees::TYPE_FUNDTRANSFER: $val = gettext('Fund transfer');
            break;
            case fees::TYPE_FUNDOUT: $val = gettext('Fundout');
            break;
            case fees::TYPE_SETTLEMENT: $val = gettext('Settlement');
            break;
            case fees::TYPE_FUNDOUTFAIL: $val = gettext('Fundout fail');
            break;
            case fees::TYPE_FASTFUNDOUT: $val = gettext('Fast fundout');
            break;
            case fees::TYPE_FASTSETTLEMENT: $val = gettext('Fast settlement');
            break;
            case fees::TYPE_TOPUP: $val = gettext('Topup');
            break;
            default:
                $val = gettext('Unknown');
        }
        return $val;
    }

    /**
     * This is customised implementation to format a message.
     *
     * @param  App                   	$app       	Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string             		$fieldValue Value of data to do translation on
     * 
     * @return string                 	BUY / SELL
     */
    private function translate_ordertype($app, EventTrigger $trigger, IObservation $state, $object, $fieldValue)
    {
        $return = 'unknown';
        if ($object->{$fieldValue} == Order::TYPE_COMPANYBUY){
            $return = 'CustomerSell';
        }
        if ($object->{$fieldValue} == Order::TYPE_COMPANYSELL){
            $return = 'CustomerBuy';
        }
        if ($object->{$fieldValue} == Order::TYPE_COMPANYBUYBACK){
            $return = 'BUYBACK';
        }
        return $return;
    }

    /**
     * This is customised implementation to format a message.
     *
     * @param  App                   	$app       	Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string             		$fieldValue Value of data to do translation on
     * 
     * @return string                 3 decimal
     */
    private function translate_numberformat3($app, EventTrigger $trigger, IObservation $state, $object, $fieldValue)
    {
        return $this->translate_numberformat($object->{$fieldValue}, 3);
    }


    /**
     * This is customised implementation to format a message.
     *
     * @param  App                   	$app       	Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string             		$fieldValue Value of data to do translation on
     * 
     * @return string                 2 decimal
     */
    private function translate_numberformat2($app, EventTrigger $trigger, IObservation $state, $object, $fieldValue)
    {
        return $this->translate_numberformat($object->{$fieldValue}, 2);
    }

    /**
     * This is customised implementation to format a message.
     *
     * @param  App                   	$app       	Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string             		$fieldValue Value of data to do translation on
     * 
     * @return string                 	3 decimal
     */
    private function translate_numberformat($value, $decimal = 2)
    {
        return number_format($value, $decimal);
    }

    /**
     * This is customised implementation to format a message.
     *
     * @param  App                   	$app       	Application
     * @param  Snap\object\eventtrigger $trigger    The event trigger object
     * @param  IObservable              $generator  The observable object
     * @param  IObservation             $state      The observation object
     * @param  Snap\object\             $object     The object being notified on
     * @param  string             		$fieldValue Value of data to do translation on
     * 
     * @return string                 	3 decimal
     */
    private function translate_getcorefinalprice($app, EventTrigger $trigger, IObservation $state, $object, $fieldValue)
    {
        // fieldview == id
        $spotorder = $app->orderStore();
        $order = $spotorder->getById($object->{$fieldValue});
        if (!$order){
            return 'CONTACT_ADMINISTRATIVE';
        }
        $final_price = $order->price + ($order->fee);
        return number_format($final_price, 3);
    }

    private function translate_getcoreproduct($app, EventTrigger $trigger, IObservation $state, $object, $fieldValue)
    {
        $productStore = $app->productStore();
        $product = $productStore->getById($object->{$fieldValue});
        if (!$product){
            return 'unknown';
        }
        return $product->name;
    }

    private function translate_usernamefrompartnerid($app, EventTrigger $trigger, IObservation $state, $object, $fieldValue)
    {
        $partner = $app->partnerStore()->getById($object->{$fieldValue});
        $user = $app->userStore()->searchTable()->select()->where('partnerid', $partner->id)->orderby('id', "ASC")->one();
        return $user->username;
    }
}
?>