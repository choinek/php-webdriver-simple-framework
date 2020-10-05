<?php

namespace Choinek\PhpWebDriverSimpleFramework;

use Facebook\WebDriver\Chrome\ChromeOptions;
use \Facebook\WebDriver\Remote\DesiredCapabilities;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Choinek\PhpWebDriverSimpleFramework\Abstracts\TestAbstract;
use Choinek\PhpWebDriverSimpleFramework\Exceptions\Failure;

/**
 * Class Bootstrap
 * @author Adrian Chojnicki <adrian@chojnicki.pl>
 */
class Bootstrap
{
    public $testDir;
    public $seleniumServerUrl;
    public $desiredCapabilities;
    public $connectionTimeoutInMs;
    public $requestTimeoutInMs;
    public $httpProxy;
    public $httpProxyPort;

    public $browsers = [];
    public $resolutions = [];

    public $logName;

    /**
     * @param $testDir
     * @param string $seleniumServerUrl
     * @param null $desiredCapabilities
     * @param null $connectionTimeoutInMs
     * @param null $requestTimeoutInMs
     * @param null $httpProxy
     * @param null $httpProxyPort
     * @return Bootstrap
     */
    static function create($testDir,
                           $seleniumServerUrl = 'http://localhost:4444/wd/hub',
                           $desiredCapabilities = null,
                           $connectionTimeoutInMs = null,
                           $requestTimeoutInMs = null,
                           $httpProxy = null,
                           $httpProxyPort = null)
    {
        $bootstrap = new self;
        $bootstrap->testDir = $testDir;
        $bootstrap->seleniumServerUrl = $seleniumServerUrl;
        $bootstrap->desiredCapabilities = $desiredCapabilities;
        $bootstrap->connectionTimeoutInMs = $connectionTimeoutInMs;
        $bootstrap->requestTimeoutInMs = $requestTimeoutInMs;
        $bootstrap->httpProxy = $httpProxy;
        $bootstrap->httpProxyPort = $httpProxyPort;

        return $bootstrap;
    }


    public function addBrowser($browser)
    {
        $this->browsers[] = $browser;
    }

    /**
     * @param $width
     * @param $height
     * @param string $name optional - if not assigned widthXheight will be used
     */
    public function addResolution($width, $height, $name = '')
    {
        if (!$name) {
            $name = $width . 'x' . $height;
        }

        $this->resolutions[$name] = [
            'width'  => $width,
            'height' => $height
        ];
    }

    /**
     * @todo refactor whole function, its quick static mockup only
     * @throws \Exception
     */
    public function run()
    {
        foreach ($this->browsers as $browser) {
            foreach ($this->resolutions as $resolution) {

                echo "Run resolution: " . $resolution['width'] . 'x' . $resolution['height'] . "\n";
                /** todo rest browsers */

                $driver = $this->prepareRemoteWebDriver($browser, $resolution);

                $groupsPath = glob($this->testDir . '/*', GLOB_ONLYDIR);

                foreach ($groupsPath as $groupPath) {
                    $groupConfigPath = $groupPath . '/config.php';
                    if (!is_file($groupConfigPath)) {
                        throw new \Exception('File ' . $groupConfigPath . ' does not exist! Did you forget about it?');
                    }
                    $groupConfig = require $groupConfigPath;
                    if ($groupConfig['active']) {
                        /** @todo interface */
                        echo "= Run test group: {$groupConfig['name']} =\n";
                        $testsPath = glob($groupPath . '/*', GLOB_ONLYDIR);
                        foreach ($testsPath as $testPath) {
                            $testConfigPath = $testPath . '/config.php';
                            if (!is_file($groupConfigPath)) {
                                throw new \Exception('File ' . $groupConfigPath . ' does not exist! Did you forget about it?');
                            }
                            /** @var array $testConfigPath */
                            $testConfig = require $testConfigPath;

                            $pathInfo = implode('/', array_slice(explode('/', $testPath), -2, 2, true));

                            if ($testConfig['active']) {
                                echo "== Run test: {$testConfig['name']} ==\n";

                                try {

                                    if (isset($testConfig['prerun']) && is_array($testConfig['prerun']) && $testConfig) {
                                        foreach ($testConfig['prerun'] as $preTestClassName) {
                                            /** @var TestAbstract $test */
                                            echo "=== Run pretest: {$preTestClassName} ===\n";
                                            $test = new $preTestClassName($driver);
                                            $test->run();
                                        }
                                    }

                                    $className = $testConfig['className'];

                                    /** @var TestAbstract $test */
                                    $test = new $className($driver);

                                    $test->run();
                                    $this->logInfo("Test {$pathInfo} ({$testConfig['name']}) succeed\n");

                                } catch (Failure $e) {

                                    $this->logInfo("Test {$pathInfo} ({$testConfig['name']}) failed: " . $e->getMessage() . "\n", 'failure');

                                } catch (\Exception $e) {

                                    $this->logInfo("Test {$pathInfo} ({$testConfig['name']}) has unhandled exception:\n" . get_class($e) . ' : ' . $e->getMessage() . "\n", 'exception');
                                }
                            }
                        }
                    }
                }

                echo "Tests for this resolution finished.\n";

                $driver->quit();
            }
        }

        echo "Ultimate finish!\n";
    }

    /**
     * @param $message
     * @param string $type
     */
    public function logInfo($message, $type = 'success')
    {
        if (!$this->logName) {
            $this->logName = 'run_' . date('Ymd_His');
        }

        echo $message;
        $path = APP_DIR . '/run-logs/' . $this->logName . '_' . $type . '.log';
        file_put_contents($path, $message);
    }

    /**
     * @param $browser
     * @param $resolution
     * @return RemoteWebDriver
     */
    public function prepareRemoteWebDriver($browser, $resolution)
    {
        try {
            $options = new ChromeOptions();
//            $options->addArguments(array('--window-size=1000,1000', '--accept-ssl-certs=true'));

            $driver = RemoteWebDriver::create($this->seleniumServerUrl, DesiredCapabilities::chrome());
            $driver->manage()->window()->maximize();
//    $driver->manage()->window()->setSize(new WebDriverDimension($resolution['width'], $resolution['height']));
        } catch (\Exception $e) {
            echo "There was an error while connecting ChromeDriver to browser - check Chrome version\n";
            echo "Error: \n" . $e->getMessage();
            exit;
        }

        return $driver;
    }

    /**
     * Send message to current input line
     *
     * @param $message
     */
    public function sendMessage($message)
    {
        echo $message . "\n";
    }


}
