<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Exception;
use Snap\IObservable;
use Snap\TObservable;
use Snap\TLogging;

use Snap\object\MyAccountHolder;
use Snap\object\MyAmlaScanLog;
use Snap\object\MyScreeningList;
use Snap\object\MyGtpEventConfig;
use Snap\object\MyLocalizedContent;
use Snap\object\MyScreeningMatchLog;
use Snap\object\Partner;
use Snap\util\amla\MohaPdfParser;
use Snap\util\amla\BnmJsonParser;
use Snap\util\amla\UnXmlParser;

/**
 * This class handles account holder management
 *
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @created 03-Nov-2020
 */
class MyGtpScreeningManager implements IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Import list from the UN source file
     *
     * @param  string $file
     * @return void
     */
    public function importMohaList($file)
    {
        try {
            $this->log(__METHOD__ . "() Begin importing records from {$file}", SNAP_LOG_DEBUG);
            $alreadyInTransaction = $this->app->getDbHandle()->inTransaction();
            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->beginTransaction();
            }

            $parser = new MohaPdfParser;
            $records  = $parser->parseData($file);

            if (empty($records)) {
                throw new Exception("Unable to parse PDF into records.");
            }

            $this->deleteOldImports(MyScreeningList::SOURCE_MOHA);

            foreach ($records as $record) {
                $this->saveRecord($record, [
                    'icno' => trim(str_replace('-', '', $record['icno'])),
                    'listedon' => $record['listedon'] ? date('Y-m-d', strtotime($record['listedon'])) : null,
                    'type' => MyScreeningList::TYPE_INDIVIDUAL,
                    'sourcetype' => MyScreeningList::SOURCE_MOHA,
                    'status' => MyScreeningList::STATUS_ACTIVE,
                    'importedon' => new \DateTime(gmdate('Y-m-d\TH:i:s\Z')),
                ]);
            }

            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->commit();
            }

            $this->log(__METHOD__ . "() Finished importing records from {$file}", SNAP_LOG_DEBUG);
        } catch (\Exception $e) {

            if (!$alreadyInTransaction && $this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }

            $this->log(__METHOD__ . "() Error while trying to import records from {$file}. " . $e->getMessage(), SNAP_LOG_ERROR);

            throw $e;
        }
    }

    /**
     * Import list from the UN source file
     *
     * @param  string $file
     * @return void
     */
    public function importUnList($file)
    {
        try {
            $this->log(__METHOD__ . "() Begin importing records from {$file}", SNAP_LOG_DEBUG);
            $alreadyInTransaction = $this->app->getDbHandle()->inTransaction();
            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->beginTransaction();
            }

            $parser = new UnXmlParser;
            $records  = $parser->parseData($file);

            if (empty($records)) {
                throw new Exception("Unable to parse XML into records.");
            }

            $this->deleteOldImports(MyScreeningList::SOURCE_UN);

            foreach ($records as $record) {
                $this->saveRecord($record, [
                    'icno' => trim(str_replace('-', '', $record['icno'])),
                    'listedon' => $record['listedon'] ? date('Y-m-d', strtotime($record['listedon'])) : null,
                    'type' => MyScreeningList::TYPE_INDIVIDUAL,
                    'sourcetype' => MyScreeningList::SOURCE_UN,
                    'status' => MyScreeningList::STATUS_ACTIVE,
                    'importedon' => new \DateTime(gmdate('Y-m-d\TH:i:s\Z')),
                ]);
            }

            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->commit();
            }

            $this->log(__METHOD__ . "() Finished importing records from {$file}", SNAP_LOG_DEBUG);
        } catch (\Exception $e) {

            if (!$alreadyInTransaction && $this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }

            $this->log(__METHOD__ . "() Error while trying to import records from {$file}. " . $e->getMessage(), SNAP_LOG_ERROR);

            throw $e;
        }
    }

    /**
     * Import list from the BNM source.
     *
     * @param  string $url
     * @return void
     */
    public function importBnmList($fileOrUrl, $isApi = true)
    {
        try {
            $location = 'API';
            $this->log(__METHOD__ . "() Begin importing records from {$location}", SNAP_LOG_DEBUG);
            $alreadyInTransaction = $this->app->getDbHandle()->inTransaction();
            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->beginTransaction();
            }

            $parser = new BnmJsonParser;
            $records  = $parser->parseData($fileOrUrl, $isApi);

            if (empty($records)) {
                throw new Exception("Unable to parse JSON into records.");
            }

            $this->deleteOldImports(MyScreeningList::SOURCE_BNM);

            foreach ($records as $record) {
                $this->saveRecord($record, [
                    'listedon' => $record['listedon'] ? date('Y-m-d', strtotime($record['listedon'])) : null,
                    'type' => MyScreeningList::TYPE_BUSINESS,
                    'sourcetype' => MyScreeningList::SOURCE_BNM,
                    'status' => MyScreeningList::STATUS_ACTIVE,
                    'importedon' => new \DateTime(gmdate('Y-m-d\TH:i:s\Z')),
                ]);
            }

            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->commit();
            }

            $this->log(__METHOD__ . "() Finished importing records from {$location}", SNAP_LOG_DEBUG);
        } catch (\Exception $e) {

            if (!$alreadyInTransaction && $this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }

            $this->log(__METHOD__ . "() Error while trying to import records from {$location}. " . $e->getMessage(), SNAP_LOG_ERROR);

            throw $e;
        }
    }

    /**
     * Import the list from all source
     *
     * @return void
     */
    public function importAllAmlaList()
    {
        try {
            $this->log(__METHOD__ . "() Begin importing records from all sources", SNAP_LOG_DEBUG);

            $alreadyInTransaction = $this->app->getDbHandle()->inTransaction();
            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->beginTransaction();
            }

            $this->importUnList($this->app->getConfig()->{'un.xml.file'}); // Use remote file
            $this->importMohaList($this->app->getConfig()->{'moha.pdf.file'}); // Use remote file
            $this->importBnmList($this->app->getConfig()->{'bnm.json.api.url'}); // Use api

            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->commit();
            }

            $this->log(__METHOD__ . "() Finished importing records from all sources", SNAP_LOG_DEBUG);
        } catch (\Exception $e) {

            if (!$alreadyInTransaction && $this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }

            $this->log(__METHOD__ . "() Error while trying to import records from all sources. " . $e->getMessage(), SNAP_LOG_ERROR);

            throw $e;
        }
    }

    /**
     * Check if account holder pass the screening and either to blacklist user
     * immediately or set as pending for the admin to take action
     *
     * @param  MyAccountHolder $accountHolder
     * @param  bool             $markAsBlacklisted
     * @param  bool             $skipScreening          False to skip screening process
     * @return void
     */
    public function screenAccountHolder(MyAccountHolder $accountHolder, $markAsBlacklisted = true, /** development param */ $skipScreening = false)
    {
        $oldAmlaStatus = $accountHolder->amlastatus;
        if (! $skipScreening) {
            if ($this->matchesScreeningList($accountHolder, $markAsBlacklisted)) {
                $this->log(__METHOD__ . "() Screening list matches for account holder: " . $accountHolder->id, SNAP_LOG_DEBUG);
                $this->markAccountHolderAsAmlaFailed($accountHolder, $markAsBlacklisted);

                return false;
            }

            $this->log(__METHOD__ . "() Mark AMLA status as passed for account holder: " . $accountHolder->id, SNAP_LOG_DEBUG);
            $accountHolder->amlastatus = MyAccountHolder::AMLA_PASSED;
            $accountHolder->verifiedon = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $accountHolder = $this->app->myaccountholderStore()->save($accountHolder);

            // Check if pep, so we won't notify the user yet
            if ($accountHolder->isPep()) {
                $this->log(__METHOD__ . "() Skipping notification for PEP account holder", SNAP_LOG_DEBUG);
                return true;
            }
        }

        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_APPROVE,
            $oldAmlaStatus,
            [
                'event' => MyGtpEventConfig::EVENT_EKYC_RESULT_PASSED,
                'projectbase' => $this->app->getConfig()->{'projectBase'},
                'accountholdercode' => $accountHolder->accountholdercode,
                'mykadno' => $accountHolder->mykadno,
                'name' => $accountHolder->fullname,
                'receiver' => $accountHolder->email,
                'accountholderid' => $accountHolder->id
            ]
        );

        $this->notify($observation);
        return true;
    }

    /**
     * Do the screening for all of the account holders who have passed the verification previously
     * with the AMLA screening. Returns list of failed verification account holders
     *
     * @return MyAccountHolder[] $failedVerifications
     */
    public function reverifyAccountHolders()
    {
        $amlaScanLog = $this->app->myamlascanlogStore()->create([
            'scannedon' => new \DateTime(gmdate('Y-m-d\TH:i:s\Z')),
            'status'    => MyAmlaScanLog::STATUS_ACTIVE,
        ]);

        $amlaScanLog = $this->app->myamlascanlogStore()->save($amlaScanLog);
        $amlaScanLogId = $amlaScanLog->id;

        /** @var MyAccountHolder[] $accountHolders */
        $accountHolders = $this->app->myaccountholderStore()->searchTable()
        ->select([])
        ->where('amlastatus', '!=', MyAccountHolder::AMLA_FAILED)
        ->where('status', MyAccountHolder::STATUS_ACTIVE)
        ->get();

        $failedVerifications = [];
        $settings = [];

        foreach($accountHolders as $accountHolder) {

            $partnerId = $accountHolder->partnerid;
            $settings[$partnerId] = $settings[$partnerId] ?? $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);
            $immediatelyBlacklist = filter_var($settings[$partnerId]->amlablacklistimmediately, FILTER_VALIDATE_BOOLEAN);

            if ($this->matchesScreeningList($accountHolder, $immediatelyBlacklist, $amlaScanLogId)) {
                $this->log(__METHOD__ . "() Found screening list matches for account holder: " . $accountHolder->id, SNAP_LOG_DEBUG);
                $this->markAccountHolderAsAmlaFailed($accountHolder, $immediatelyBlacklist);
                $failedVerifications[] = $accountHolder;
            }
        }

        return $failedVerifications;
    }

    /**
     * This method mark the account holder as AMLA failed or pending. If markAsBlacklisted is false, then
     * the AMLA status is pending for the account holder
     *
     * @param  MyAccountHolder $accountHolder
     * @param  boolean $markAsBlacklisted
     * @return void
     */
    protected function markAccountHolderAsAmlaFailed($accountHolder, $markAsBlacklisted)
    {
        if ($markAsBlacklisted) {
            $oldStatus = $accountHolder->status;
            $accountHolder->amlastatus = MyAccountHolder::AMLA_FAILED;
            $accountHolder->status     = MyAccountHolder::STATUS_BLACKLISTED;

            $observation = new \Snap\IObservation(
                $accountHolder,
                \Snap\IObservation::ACTION_FREEZE,
                $oldStatus,
                [
                    'event' => MyGtpEventConfig::EVENT_AMLA_FAILED,
                    'projectbase' => $this->app->getConfig()->{'projectBase'},
                    'accountholdercode' => $accountHolder->accountholdercode,
                    'mykadno' => $accountHolder->mykadno,
                    'name' => $accountHolder->fullname,
                    'receiver' => $accountHolder->email
                ]
            );

            $this->log(__METHOD__ . "() Mark AMLA status as failed for account holder: " . $accountHolder->id, SNAP_LOG_DEBUG);
            $this->notify($observation);

        } else {
            $this->log(__METHOD__ . "() Mark AMLA status as pending for account holder: " . $accountHolder->id, SNAP_LOG_DEBUG);
            $accountHolder->amlastatus = MyAccountHolder::AMLA_PENDING;
        }

        $accountHolder->verifiedon = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
        $this->app->myaccountholderStore()->save($accountHolder);
    }

    /**
     * This method check if the account holder matches the screening list
     *
     * @param  MyAccountHolder   $accountHolder
     * @param  boolean           $markAsBlacklisted
     * @param  int               $amlaScanLogId
     * @return boolean
     */
    protected function matchesScreeningList($accountHolder, $markAsBlacklisted = true, $amlaScanLogId = 0)
    {
        $matches = $this->app->myscreeninglistStore()
            ->searchTable()
            ->select([])
            ->where('icno', 'LIKE', '%' . $accountHolder->mykadno . '%')
            ->where('status', MyScreeningList::STATUS_ACTIVE)
            ->get();

        if (0 < count($matches)) {
            $this->log(__METHOD__ . "() Matches found for screening log: " . count($matches), SNAP_LOG_DEBUG);

            foreach ($matches as $match) {
                $matchLog = $this->app->myscreeningmatchlogStore()->create([
                    'screeninglistid' => $match->id,
                    'accountholderid' => $accountHolder->id,
                    'amlascanlogid' => $amlaScanLogId,
                    'matcheddata' => $match->icno,
                    'status' => $markAsBlacklisted ? MyScreeningMatchLog::STATUS_BLACKLISTED : MyScreeningMatchLog::STATUS_PENDING,
                    'matchedon' => new \DateTime(gmdate('Y-m-d\TH:i:s\Z')),
                ]);

                $this->app->myscreeningmatchlogStore()->save($matchLog);
            }

            return true;
        }

        return false;
    }

    protected function deleteOldImports($source)
    {
        $db        = $this->app->getDBHandle();
        $store     = $this->app->myscreeninglistStore();
        $prefix    = $store->getColumnPrefix();
        $tableName = $store->getTableName();
        $now       = (new \DateTime('now'))->format('Y-m-d H:i:s');
        
        // Set triggeredon and triggered
        $wheres = sprintf("{$prefix}sourcetype = '%s'", $source);
        $fields  = sprintf("{$prefix}modifiedon = '%s', {$prefix}status = %d", $now, MyScreeningList::STATUS_INACTIVE);
        $res     = $db->query("UPDATE {$tableName} SET {$fields} WHERE {$wheres}");
    }

    /**
     * Save the record into the database
     *
     * @param  array $record
     * @param  array $overwriteRecord
     * @return void
     */
    private function saveRecord($record, $overwriteRecord)
    {
        $screeningListStore = $this->app->myscreeninglistStore();
        $screeningList      = $screeningListStore->create(array_merge($record, $overwriteRecord));
        $screeningListStore->save($screeningList, ['name', 'alias', 'icno', 'dob', 'alias', 'address', 'type', 'sourcetype', 'status', 'listedon', 'importedon',]);
    }

}
