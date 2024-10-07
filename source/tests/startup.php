?<?php
define('TEST_DIR', dirname(__FILE__));
define('SNAPLIB_DIR', dirname(TEST_DIR).DIRECTORY_SEPARATOR.'snaplib' . DIRECTORY_SEPARATOR);
define('SNAPAPP_DIR', dirname(TEST_DIR).DIRECTORY_SEPARATOR.'snapapp' . DIRECTORY_SEPARATOR);
define('SNAPAPP_MODE_CLI', 1);
define('SNAP_MODE_NO_USERSESSION',1);
define('TEST_CONFIG_FILE', 'testconfig.ini');
define('TEST_TABLE_PREFIX', 'test_');
include_once SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'app.php';
include_once 'LazyTestCase.php';

class gtptestcontroller extends \Snap\gtpcontroller
{
    private $tableMappings = array();
    private $viewMappings = array();

    /**
     * Convert the table names to prefix with test for testing purposes.
     */
    protected function registerStore($storeKey, $initialData, $publicAccessible = true)
    {
        if(! preg_match('/(.*)(lazystore|lazyfactory)$/i', $storeKey)) {
            $tableIdx = 1;
            $viewIdx = 5;
            if("\\Snap\\store\\dbdatastore" != $initialData['class'] && preg_match('/dbdatastore/', $initialData['class'])) {
                $dataCopy = $initialData['parameters'];
                unset($dataCopy[0]);
                $initialData['parameters'] = array();
                foreach($dataCopy as $data) {
                    $initialData['parameters'][] = $data;
                }
                $initialData['class'] = "\\Snap\\store\\dbdatastore";
            } elseif(! preg_match('/dbdatastore/', $initialData['class'])) {
                $tableIdx = 2;
                $viewIdx = 6;
            }
            $this->tableMappings[$storeKey] = array( 
                    'testTable' => 'test_' . $initialData['parameters'][$tableIdx], 
                    'originalTable' => $initialData['parameters'][$tableIdx]);
            $initialData['parameters'][$tableIdx] = 'test_' . $initialData['parameters'][$tableIdx];
            $testViews = $initialData['parameters'][$viewIdx];
            if(count($initialData['parameters'][$viewIdx])) {
                foreach($initialData['parameters'][$viewIdx] as $viewName) {
                    $testViewName = preg_replace('/^vw_/', 'vw_test', $viewName);
                    $testViews[] = $testViewName;
                    $this->viewMappings[$testViewName] = $viewName;
                }
                $initialData['parameters'][$viewIdx] = $testViews;
            }            
        }
        return parent::registerStore($storeKey, $initialData, $publicAccessible);
    }

    protected function startupStore($storeKey, $level = array())
    {
        if(! preg_match('/(.*)(lazystore|lazyfactory)$/i', $storeKey)) {
            $replaceTableName = $replaceTestTableName = array();
            foreach($this->tableMappings as $aMapping) {
                $replaceTableName[] = '/ `' . $aMapping['originalTable'] . '`/';
                $replaceTestTableName[] = ' `' . $aMapping['testTable']  .'`';
            }
            $tableInfo = $this->tableMappings[$storeKey];
            $dbHandle = \Snap\App::getInstance()->getDBHandle();
            $dbHandle->query("create table `{$tableInfo['testTable']}` like `{$tableInfo['originalTable']}`;");
            $dbHandle->query("insert into `{$tableInfo['testTable']}` select * from `{$tableInfo['originalTable']}`;");
            foreach($this->viewMappings as $testViewName => $orgViewName) {
                $replaceTableName[] = '/`' . $orgViewName . '`/';
                $replaceTestTableName[] = '`' . $testViewName . '`';

                $res = $dbHandle->query("show create view $orgViewName;");
                $row = $res->fetch(\PDO::FETCH_ASSOC);
                $sql = $row['Create View'];
                $sql = preg_replace($replaceTableName, $replaceTestTableName, $sql);
                $dbHandle->query($sql);
            }
        }
        return parent::startupStore($storeKey, $level);
    }

    protected function registerManager($managerKey, $managerClass, $observableTargets = array())
    {
        return parent::registerManager($managerKey, $managerClass, array());
    }

    public function __destruct()
    {
        $dbHandle = \Snap\App::getInstance()->getDBHandle();
        if(! $dbHandle) return;
        foreach($this->tableMappings as $tableArray) {
            $dbHandle->query("drop table if exists {$tableArray['testTable']};");
        }
        foreach($this->viewMappings as $testViewName => $orgViewName) {
            $dbHandle->query("drop view if exists $testViewName");
        }
    }
}

// include_once 'gtptestcontroller.php';
$app = \Snap\App::getInstance(TEST_DIR . DIRECTORY_SEPARATOR . 'testconfig.ini', SNAPAPP_MODE_CLI);
$app->run(SNAP_MODE_NO_USERSESSION, null, null, null, array());
?>