<?php

namespace Choinek\PhpWebDriverSimpleFramework\Functions;

use Choinek\PhpWebDriverSimpleFramework\Helpers\Registry;

/**
 * Class DataFile
 * @package Choinek\PhpWebDriverSimpleFramework\Functions
 */
class DataFile {

    /**
     * @throws Exception
     */
    public static function importDataFile(string $name): array
    {
        if (!defined('APP_DIR')) {
            throw new Exception ('APP_DIR was not defined in run.php');
        }

        $environment = Registry::getData('environment', Registry::CONFIG_NAMESPACE);

        if ($environment) {
            $filePath = APP_DIR . DIRECTORY_SEPARATOR . 'data-file-environment' . DIRECTORY_SEPARATOR . $environment . DIRECTORY_SEPARATOR . $name;
        } else {
            $filePath = APP_DIR . DIRECTORY_SEPARATOR . 'data-file' . DIRECTORY_SEPARATOR . $name;
        }

        if (is_file($filePath)) {
            $fileData = file_get_contents($filePath);
            /** @var array $jsonData */
            $jsonData = json_decode($fileData, true);

            if ($jsonData) {
                return $jsonData;
            } else {
                throw new Exception('Data file: ' . $name . ' - wrong or empty json.');
            }

        } else {
            throw new \Exception ("Data file: $name was not found.");
        }
    }
}
