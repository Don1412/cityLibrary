<?php


namespace Classes;


use Interfaces\LogParser;

class CsvParser implements LogParser
{
    private $logsDirectory;

    public function __construct($logsDirectory)
    {
        $this->logsDirectory = $logsDirectory;
    }

    private function prepareArray($array)
    {
        $preparedArray = [];
        $keys = array_values($array[0]);
        unset($array[0]);
        foreach ($array as $record) {
            $tempArray = [];
            foreach ($record as $key => $value) {
                $tempArray[$keys[$key]] = $value;
            }
            $preparedArray[] = $tempArray;
        }

        return $preparedArray;
    }

    public function getLogsArray()
    {
        $logsArray = [];
        foreach (glob("{$this->logsDirectory}/*csv") as $file) {
            $xmlLog = array_map('str_getcsv', file($file));
            $jsonLogs = json_encode($xmlLog);
            $logsArray = array_merge($logsArray, json_decode($jsonLogs, true));
        }

        return $this->prepareArray($logsArray);
    }
}