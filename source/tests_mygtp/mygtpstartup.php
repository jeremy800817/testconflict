?<?php
define('TEST_DIR', dirname(__FILE__));
define('SNAPLIB_DIR', dirname(TEST_DIR).DIRECTORY_SEPARATOR.'snaplib' . DIRECTORY_SEPARATOR);
define('SNAPAPP_DIR', dirname(TEST_DIR).DIRECTORY_SEPARATOR.'snapapp' . DIRECTORY_SEPARATOR);
define('MOCKS_DIR', TEST_DIR.DIRECTORY_SEPARATOR.'mocks' . DIRECTORY_SEPARATOR);
define('SNAPAPP_MODE_CLI', 1);
define('SNAP_MODE_NO_USERSESSION',1);
define('TEST_CONFIG_FILE', 'mygtptestconfig.ini');
define('TEST_TABLE_PREFIX', 'test_');
include_once SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'app.php';
include_once 'BaseTestCase.php';
include_once 'AuthenticatedTestCase.php';

class mygtptestcontroller extends \Snap\mygtpcontroller
{
    private $tableMappings = array();
    private $viewMappings = array();

    protected $skipCopyTable = [
        'apilogs'
    ];

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
            } 
            elseif(! preg_match('/dbdatastore/', $initialData['class']) && 
                     get_class($initialData['parameters'][0]) == get_class($this->app->getCacher())) {
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
                    $this->viewMappings[$viewName] = $testViewName;
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
            foreach($this->tableMappings as $k => $aMapping) {
                $replaceTableName[] = '/`' . $aMapping['originalTable'] . '`/';
                $replaceTestTableName[] = '`' . $aMapping['testTable']  .'`';
            }
            $tableInfo = $this->tableMappings[$storeKey];
            $dbHandle = \Snap\App::getInstance()->getDBHandle();
            $stmt = $dbHandle->query("create table `{$tableInfo['testTable']}` like `{$tableInfo['originalTable']}`;");
            if (!in_array($tableInfo['originalTable'], $this->skipCopyTable)) {
                $stmt = $dbHandle->query("insert into `{$tableInfo['testTable']}` select * from `{$tableInfo['originalTable']}`;");
            }
            if (is_array($this->fulldatastores[$storeKey])) {
                $parameters = $this->fulldatastores[$storeKey]['parameters'];
                $viewIdx = (get_class($parameters[0]) == get_class($this->app->getCacher())) ? 6 : 5 ;
                $viewTables = $parameters[$viewIdx];
                $usedViews = [];
                foreach ($viewTables as $testViewName) {
                    // Only create view when necessary
                    
                    // Some views cannot be created due to dependency on another view
                    // Solution is to manually call the depended store before the test in order to initialise the view
                    // Cyclical dependency can be broken by calling the store again
                    $orgViewName = array_search($testViewName, $this->viewMappings);
                    if ($orgViewName) {
                        $replaceTableName[] = "/`$orgViewName`/";
                        $replaceTestTableName[] = "`$testViewName`";

                        $res = $dbHandle->query("show create view $orgViewName;");
                        $row = $res->fetch(\PDO::FETCH_ASSOC);
                        $sql = $row['Create View'];
                        $sql = preg_replace($replaceTableName, $replaceTestTableName, $sql);
                        $sql = preg_replace("/DEFINER=\S+/",'', $sql);
                        $stmt = $dbHandle->query($sql);
                        if (!$stmt) {
                            echo "\nError while creating table $testViewName : ";
                            echo $dbHandle->errorInfo()[2]."\n";
                        } else {
                            $usedViews[] = $testViewName;
                        }
                    }
                }
                $this->fulldatastores[$storeKey]['parameters'][$viewIdx] = $usedViews;
            }
        }
        return parent::startupStore($storeKey, $level);
    }

    protected function registerManager($managerKey, $managerClass, $observableTargets = array())
    {
        return parent::registerManager($managerKey, $managerClass, $observableTargets);
    }

    public function persistTestStore($storeName) {
        $dbHandle = \Snap\App::getInstance()->getDBHandle();
        $store = $this->datastores[$storeName];
        $tableInfo = $this->tableMappings[$storeName];
        if (! $tableInfo || is_array($store)) {
            return;
        }

        $pre = $store->getColumnPrefix();
        $orgTable = $tableInfo['originalTable'];
        $testTable = $tableInfo['testTable'];

        $stmt = $dbHandle->query("insert into `$orgTable` select * from `$testTable` where $testTable.{$pre}id > IFNULL((SELECT MAX({$pre}id) from $orgTable), 0);");
    }

    public function __destruct()
    {
        $dbHandle = \Snap\App::getInstance()->getDBHandle();
        if(! $dbHandle) return;
        foreach($this->tableMappings as $tableArray) {
            $dbHandle->query("drop table if exists {$tableArray['testTable']};");
        }
        foreach($this->viewMappings as $orgViewName => $testViewName) {
            $dbHandle->query("drop view if exists $testViewName");
        }
    }
}

// include_once 'gtptestcontroller.php';
$app = \Snap\App::getInstance(TEST_DIR . DIRECTORY_SEPARATOR . TEST_CONFIG_FILE, SNAPAPP_MODE_CLI);
$app->run(SNAP_MODE_NO_USERSESSION, null, null, null, array());
?>