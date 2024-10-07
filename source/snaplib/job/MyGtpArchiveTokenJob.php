<?php

namespace Snap\job;

use Snap\App;
use Snap\object\MyToken;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 08-Jan-2021
*/

final class MyGtpArchiveTokenJob extends basejob
{

    /**
     * Archive expired/inactive tokens
     * 
     * @param App $app 
     * @param array $params 
     * @return void 
     */
    function doJob($app, $params = array())
    {
        $now = new \DateTime();
        $tokenStore = $app->mytokenStore();

        // Collect expired & inactive tokens
        $expiredTokens = $tokenStore->searchTable()->select()
                            ->where('expireon', '<=', $now->format('Y-m-d H:i:s'))
                            ->orWhere('status', MyToken::STATUS_INACTIVE)
                            ->execute();
        $ids = [];
        foreach ($expiredTokens as $token) {
            $ids[] = $token->id;
        }

        if (0 < count($ids)) {
            try {
                // Insert into archive table
                $this->insertTokensIntoArchive($app, $ids);

                // Delete from main table
                $this->deleteTokensFromTable($app, $ids);
            } catch (\Exception $e) {
                $this->log($e->getMessage(), SNAP_LOG_ERROR);
                throw $e;
            }
        }

        $this->log("Completed archiving expired & inactive tokens at " . $now->format('Y-m-d H:i:s'), SNAP_LOG_INFO);
    }

    private function insertTokensIntoArchive($app, $ids)
    {
        $tokenStore = $app->mytokenStore();
        $db = $app->getDBHandle();
        $prefix = $tokenStore->getColumnPrefix();
        $idCondition = sprintf("{$prefix}id IN (%s)", implode(",", $ids));
        $tableName = $tokenStore->getTableName();
        $archiveTableName = $tableName."_archive";
        $res = $db->query("INSERT INTO $archiveTableName SELECT * FROM $tableName WHERE $idCondition ;");
        if (!$res || 0 == $res->rowCount()) {
            throw new \Exception(__METHOD__. "(): ". json_encode($db->errorInfo()));
        }
    }

    private function deleteTokensFromTable($app, $ids)
    {
        $tokenStore = $app->mytokenStore();
        $db = $app->getDBHandle();
        $prefix = $tokenStore->getColumnPrefix();
        $idCondition = sprintf("{$prefix}id IN (%s)", implode(",", $ids));
        $tableName = $tokenStore->getTableName();
        $res = $db->query("DELETE FROM $tableName WHERE $idCondition ;");
        if (!$res || 0 == $res->rowCount()) {
            throw new \Exception(__METHOD__. "(): ". json_encode($db->errorInfo()));
        }
    }
    

    function describeOptions() {
        return [
        ];
    }
}