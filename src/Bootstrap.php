<?php

namespace Choinek\PhpWebDriverSimpleFramework;

use Choinek\PhpWebDriverSimpleFramework\Helpers\Registry;
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
    public $codes = [];
    /**
     * @var string[]
     */
    public $tags = [];
    public $environment = null;
    public $force = false;

    public $logName;


    /**
     * @param $testDir
     * @param string $seleniumServerUrl
     * @param null $desiredCapabilities
     * @param null $connectionTimeoutInMs
     * @param null $requestTimeoutInMs
     * @param null $httpProxy
     * @param null $httpProxyPort
     * @param string $baseNamespace
     * @return Bootstrap
     */
    public static function create(
        $testDir,
        $seleniumServerUrl = 'http://localhost:4444/wd/hub',
        $desiredCapabilities = null,
        $connectionTimeoutInMs = null,
        $requestTimeoutInMs = null,
        $httpProxy = null,
        $httpProxyPort = null,
        $baseNamespace = 'Tests'): Bootstrap
    {
        $bootstrap = new self;
        $bootstrap->testDir = $testDir;
        $bootstrap->seleniumServerUrl = $seleniumServerUrl;
        $bootstrap->desiredCapabilities = $desiredCapabilities;
        $bootstrap->connectionTimeoutInMs = $connectionTimeoutInMs;
        $bootstrap->requestTimeoutInMs = $requestTimeoutInMs;
        $bootstrap->httpProxy = $httpProxy;
        $bootstrap->httpProxyPort = $httpProxyPort;
        Registry::setData(Registry::CFG_BASE_NAMESPACE, $baseNamespace, Registry::CONFIG_NAMESPACE);
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
    public function addResolution($width, $height, $name = ''): void
    {
        if (!$name) {
            $name = $width . 'x' . $height;
        }

        $this->resolutions[$name] = [
            'width' => $width,
            'height' => $height
        ];
    }

    /**
     * @todo use framework like symfony cli or sth
     */
    public function parseArguments(): void
    {
        global $argv;
        $flagsAvailable = ['--env', '--codes', '--tags', '--force', '--help'];
        $getFlag = false;

        foreach ($argv as $value) {
            if ($getFlag) {
                switch ($getFlag) {
                    case '--help':
                        /** @todo write rest */
                        echo 'Available flags: --env --codes --force --help';
                        exit();
                    case '--env':
                        $this->environment = $value;
                        Registry::setData('environment', $this->environment, Registry::CONFIG_NAMESPACE);
                        echo 'Run on enviroment: ' . $this->environment . PHP_EOL;
                        break;
                    case '--codes':
                        $this->codes = explode(',', $value);
                        echo 'Run only tests with codes: ' . implode(', ', $this->codes) . PHP_EOL;
                        if ($this->tags) {
                            echo '[!] Warning - you have defined codes and tags parameters. It is not handled.' . PHP_EOL;
                        }
                        break;
                    case '--tags':
                        $this->tags = explode(',', $value);
                        echo 'Run only tests with tags: ' . implode(', ', $this->tags) . PHP_EOL;
                        if ($this->codes) {
                            echo '[!] Warning - you have defined codes and tags parameters. It is not handled.' . PHP_EOL;
                        }
                        break;
                    case '--force':
                        $this->force = $value;
                        echo 'Force running unactive' . PHP_EOL;
                        break;
                }

                $getFlag = false;
                continue;
            }

            if (in_array($value, $flagsAvailable, true)) {
                $getFlag = $value;
            }
        }
    }

    /**
     * @throws \Exception
     * @todo refactor whole function, its quick static mockup only
     */
    public function run(): void
    {
        $this->parseArguments();

        foreach ($this->browsers as $browser) {
            foreach ($this->resolutions as $resolution) {

                echo "Run resolution: " . $resolution['width'] . 'x' . $resolution['height'] . "\n";
                /** todo rest browsers */


                $configPath = $this->testDir . DIRECTORY_SEPARATOR . 'config.php';
                $config = require_once($configPath);

                if (!isset($config['tests'])) {
                    throw new \Exception($configPath . ' is not configured properly.');
                }

                foreach ($config['tests'] as $groupConfig) {

                    $retry = $groupConfig['retry'] ?? 1;

                    for ($i = 0; $i < $retry; $i++) {

                        if ($this->codes && !in_array($groupConfig['code'] ?? '', $this->codes, true)) {
                            continue;
                        }

                        if ($this->tags) {
                            $omit = true;
                            foreach ($this->tags as $tag) {
                                if (in_array($tag, $groupConfig['tags'] ?? [], true)) {
                                    $omit = false;
                                }
                            }
                            if ($omit) {
                                continue;
                            }
                        }

                        if ($groupConfig['active'] || $this->force) {
                            $driver = $this->prepareRemoteWebDriver($browser, $resolution);

                            /** @todo interface */
                            echo "= Run test group: {$groupConfig['name']} =\n";
                            foreach ($groupConfig['classes'] as $className) {
                                echo '== Run test: ' . $className::$name . "\n";

                                try {
                                    /** @var TestAbstract $test */
                                    $test = new $className($driver);

                                    $test->run();
                                    $this->logInfo("Test " . $className::$name . "\n");

                                } catch (Failure $e) {

                                    $this->logInfo("Test " . $className::$name . " failed: " . $e->getMessage() . "\n", 'failure');
                                    continue 2;
                                } catch (\Exception $e) {

                                    $this->logInfo("Test " . $className::$name . " has unhandled exception:\n" . get_class($e) . ' : ' . $e->getMessage() . "\n", 'exception');
                                    continue 2;
                                }
                            }

                            echo "= Test for group {$groupConfig['name']} finished. =\n";
                            TestAbstract::resetHelpers();
                            $driver->quit();
                        }
                    }
                }

                echo "Tests for this resolution were finished.\n";

                // @todo create some generator for error messages
                if (TestAbstract::$successes) {
                    echo 'Successful tests: ' . TestAbstract::$successes . PHP_EOL;
                }

                if (TestAbstract::$errors) {
                    echo '! Errors in project:' . PHP_EOL;
                    foreach (TestAbstract::$errors as $priority => $count) {
                        echo '! ' . TestAbstract::$errorsLabels[$priority] . ': ' . $count . PHP_EOL;
                    }
                }
            }
        }
        echo "All tests were finished.\n";
    }

    /**
     * @param DesiredCapabilities|mixed $browser
     * @param WebDriverDimension|mixed $resolution
     * @return RemoteWebDriver
     */
    public function prepareRemoteWebDriver($browser, $resolution): RemoteWebDriver
    {
        if (!($browser instanceof DesiredCapabilities)) {
            $browser = DesiredCapabilities::chrome();
        }

        try {
            $options = new ChromeOptions();
//            $options->addArguments(array('--window-size=1000,1000', '--accept-ssl-certs=true'));

            $driver = RemoteWebDriver::create($this->seleniumServerUrl, $browser);


            if ($resolution instanceof WebDriverDimension) {
                $driver->manage()->window()->setSize($resolution);
            } else {
                $driver->manage()->window()->maximize();
            }

        } catch (\Exception $e) {
            echo "There was an error while connecting ChromeDriver to browser - check Chrome version\n";
            echo "Error: \n" . $e->getMessage();
            exit;
        }

        return $driver;
    }

    /**
     * @param $message
     * @param string $type
     */
    public function logInfo($message, $type = 'success'): void
    {
        if (!$this->logName) {
            $this->logName = 'run_' . date('Ymd_His');
        }

        echo "\n[INFO] {$message}";
        $path = APP_DIR . '/run-logs/' . $this->logName . '_' . $type . '.log';
        file_put_contents($path, $message . "\n", FILE_APPEND);
    }

    /**
     * Send message to current input line
     *
     * @param $message
     */
    public function sendMessage($message): void
    {
        echo $message . "\n";
    }

}
