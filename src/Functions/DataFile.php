<?php

namespace Choinek\PhpWebDriverSimpleFramework\Functions;

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

        $filePath = APP_DIR . DIRECTORY_SEPARATOR . 'data-file' . DIRECTORY_SEPARATOR . $name;

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
