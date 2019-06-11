<?php

namespace Classes;

use Interfaces\LogParser;

class XmlParser implements LogParser
{
    private $logsDirectory;

    public function __construct($logsDirectory)
    {
        $this->logsDirectory = $logsDirectory;
    }

    private function prepareArray($array)
    {
        $preparedArray = [];
        foreach ($array['record'] as $record) {
            $tempArray = [];
            foreach ($record as $key => $attribute) {
                if (is_array($attribute)) {
                    $currentAttribute = $attribute['@attributes'];
                    $tempArray[$key] = $currentAttribute[array_keys($currentAttribute)[0]];
                } else {
                    $tempArray[$key] = $attribute;
                }
            }
            $preparedArray[] = $tempArray;
        }

        return $preparedArray;
    }

    public function getLogsArray()
    {
        $logsArray = [];
        foreach (glob("{$this->logsDirectory}/*xml") as $file) {
            $logContent = file_get_contents($file);
            $xmlLog = simplexml_load_string($logContent);
            $jsonLogs = json_encode((array)$xmlLog);
            $logsArray = array_merge($logsArray, json_decode($jsonLogs, true));
        }

        return $this->prepareArray($logsArray);
    }
}