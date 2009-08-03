<?php
/**
 *
 * Copyright (c) FaZend.com
 * All rights reserved.
 *
 * You can use this product "as is" without any warranties from authors.
 * You can change the product only through Google Code repository
 * at http://code.google.com/p/fazend
 * If you have any questions about privacy, please email privacy@fazend.com
 *
 * @copyright Copyright (c) FaZend.com
 * @version $Id$
 * @category FaZend
 */

/**
 * Manager of unit tests
 *
 * @package FaZend 
 */
class FaZend_Test_Manager {

    /**
     * Instance of the class
     *
     * @var FaZend_Test_Manager
     */
    protected static $_instance;

    /**
     * Directory with unit tests
     *
     * @var string
     */
    protected $_location;

    /**
     * Instance of the manager
     *
     * @return FaZend_Test_Manager
     */
    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new FaZend_Test_Manager();
        }

        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function __construct() {
        $this->_location = realpath(APPLICATION_PATH . '/../../test');
    }

    /**
     * Full list of unit tests
     *
     * @return string[]
     */
    public function getTests() {
        return $this->_getTests();
    }

    /**
     * Run one single test and return XML result
     *
     * phpUnit 3.3.16 is used in time of development
     *
     * @param string Name of the unit test file
     * @return array
     */
    public function run($name) {
        
        $started = microtime(true);

        $bootstrap = tempnam(TEMP_PATH, 'fzunits');
        $testdox = tempnam(TEMP_PATH, 'fzunits');
        $metrics = tempnam(TEMP_PATH, 'fzunits');
        $log = tempnam(TEMP_PATH, 'fzunits');

        // we pass ENV to the testing environment
        file_put_contents($bootstrap, 
            '<?php define("APPLICATION_ENV", "' . APPLICATION_ENV . '"); define("TESTING_RUNNING", true);');
        
        // phpUnit cmd line
        $cmd = 'phpunit --verbose --stop-on-failure' . 
            ' -d "include_path=' . ini_get('include_path') . PATH_SEPARATOR . realpath(APPLICATION_PATH . '/../library') . '"' . 
            ' --log-xml ' . $log .
            ' --bootstrap ' . $bootstrap .
            ' --testdox-text ' . $testdox .
            (extension_loaded('xdebug') ? ' --log-metrics ' . $metrics : false) .
            ' test/' . $name;

        // don't limit in time
        set_time_limit(0);
        chdir($this->_location . '/..');
        $output = shell_exec($cmd);
        unlink($bootstrap);

        $result = array(
            'output' => $cmd . "\n" . $output,
            'testdox' => file_get_contents($testdox),
            'tests' => array(),
        );

        if (file_exists($log) && file_get_contents($log)) {

            // process XML report from phpUnit
            $xml = simplexml_load_file($log);
            foreach ($xml->testsuite->children() as $tc) {
                $result['tests'][] = array(
                    'name' => (string)$tc->attributes()->name,
                    'time' => (float)$tc->attributes()->time,
                    'assertions' => (int)$tc->attributes()->assertions,
                );
            }
            $result['suite'] = array(
                'name' => (string)$xml->testsuite->attributes()->name,
                'tests' => (int)$xml->testsuite->attributes()->tests,
                'assertions' => (int)$xml->testsuite->attributes()->assertions,
                'failures' => (int)$xml->testsuite->attributes()->failures,
                'errors' => (int)$xml->testsuite->attributes()->errors,
                'time' => (float)$xml->testsuite->attributes()->time,
            );

            unlink($log);

        }

        unlink($testdox);
        unlink($metrics);
        
        // show small report
        $result['spanlog'] = sprintf('%0.2fsec', microtime(true) - $started);

        // if it's a failure - red it
        if (!isset($result['suite']) || $result['suite']['failures'] || $result['suite']['errors'])
            $result['spanlog'] = '<span style="color: #' . FaZend_Image::BRAND_RED . '">' . $result['spanlog'] . '</span>';

        $result['protocol'] = $result['testdox'];

        return $result;
        
    }

    /**
     * Get full list of unit tests, recursively called
     *
     * @param string Directory name, after $this->_location
     * @return string[]
     */
    public function _getTests($path = '.') {

        $result = array();
        foreach (glob($this->_location . '/' . $path . '/*') as $file) {

            $matches = array();
            $filePath = $path . '/' . basename($file);

            if (is_dir($file))
                $result = array_merge($result, $this->_getTests($filePath));
            elseif (preg_match('/\.\/(.*?Test).php$/', $filePath, $matches))
                $result[] = $matches[1];
        }

        // reverse sort in order to put directories on top
        rsort($result);

        // return the list of files, recursively
        return $result;

    }

}
