<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\object\MyMemberUpload;
use Snap\object\MyOccupationCategory;
use Snap\object\MyAccountHolder;
use Snap\object\MyBank;
use Snap\object\MyAddress;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Keys\Version1\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\Version1\AsymmetricSecretKey;
use ParagonIE\Paseto\Protocol\Version1;

    use ParagonIE\Paseto\Builder;
    use ParagonIE\Paseto\Purpose;
    use ParagonIE\Paseto\Parser;
    use ParagonIE\Paseto\Rules\{
        IssuedBy,
        ValidAt
    };
    use ParagonIE\Paseto\ProtocolCollection;

use Exception;

/**
 *
 * @author Rinston <rinston@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class TestEmailJob  extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()){
        try{
            $mail = $app->getMailer();
            $mail->addAddress('chen.teng.siang@silverstream.my');
            $mail->isHtml(true);
            // $email = $partner->senderemail;
            // $name = $partner->sendername;
            $mail->setFrom('arbmdigitalgoldi@alrajhibank.com.my', 'arbmdigitalgoldi');

            $mail->Subject = 'This subject';

            $mail->Body = "this email body";

            $mail->send();
        }
        catch(Exception $e){
            echo "ERROR: ".$e->getMessage()."\n";
            $this->log("ERROR: ".$e->getMessage(), SNAP_LOG_ERROR);
        }
    }
    /**
     * This method is used to display options parameter for this job.
     * @return Array of associative array of parameters.
     *         E.g.[
     *            'param1' => array('required' => true, 'type' => 'int', 'desc' => 'Some description'),
     *            'param2' => array('required' => false, 'default' => 1, type' => 'string', 'desc' => 'Some description 22222'),
     *         ]
     *         -Where [required] indicates if the params is required for the job to run.  The cli will ensure this parameter is provided
     *                [type] is the expected data type of the parameter or its valid values.
     *                [default] is the default value for the field.
     *                [desc] is the description of the parameter and what it does.
     */
    function describeOptions()
    {
        return [];
    }
}

