<?php

namespace Classes;

class LibraryLogsChecker
{
    private $logsDirectory;
    private $logs;
    const CHECK_IN = 'check-in';
    const CHECK_OUT = 'check-out';
    const JSON_FORMAT = 'json';
    const TEXT_FORMAT = 'text';

    public function __construct($logsDirectory = 'input')
    {
        if (!is_dir($logsDirectory)) {
            die('Not found directory');
        }

        $this->logsDirectory = $logsDirectory;
        if (empty($this->logs = array_merge($this->parseXmlLog(), $this->parseCsvLog()))) {
            die('Not found logs');
        }
    }

    private function parseXmlLog()
    {
        $checker = new XmlParser($this->logsDirectory);
        $log = $checker->getLogsArray();

        return $log ?? [];
    }

    private function parseCsvLog()
    {
        $checker = new CsvParser($this->logsDirectory);
        $log = $checker->getLogsArray();

        return $log ?? [];
    }

    private function prepareData()
    {
        $preparedData = [];
        foreach ($this->logs as $log) {
            $person = $log['person'];
            if (empty($preparedData['readers'][$person])) {
                $preparedData['readers'][$person] = [
                    'countBooks' => 0,
                    'countCheckOuts' => 0,
                ];
            }

            $book = $log['isbn'];
            if (empty($preparedData['books'][$book])) {
                $preparedData['books'][$book] = [
                    'timeInCheckOut' => 0,
                    'checkOut' => 0,
                    'timeCheckOut' => 0,
                ];
            }

            switch($log['action']) {
                case self::CHECK_IN:
                    $preparedData['readers'][$person]['countBooks']--;
                    $preparedData['books'][$book]['timeInCheckOut'] += $preparedData['books'][$book]['timeCheckOut']
                        - strtotime($log['timestamp']);
                    $preparedData['books'][$book]['checkOut']--;
                    break;
                case self::CHECK_OUT:
                    $preparedData['books'][$book]['timeCheckOut'] = strtotime($log['timestamp']);
                    $preparedData['books'][$book]['checkOut']++;
                    $preparedData['readers'][$person]['countBooks']++;
                    $preparedData['readers'][$person]['countCheckOuts']++;
                    break;
            }
        }

        return $preparedData;
    }

    private function getData()
    {
        $preparedData = $this->prepareData();
        $data = [
            'checkOutBooksCount' => 0,
        ];
        $maxValues = [
            'countCheckOuts' => 0,
            'countBooks' => 0,
            'timeInCheckOut' => 0,
        ];
        foreach ($preparedData['readers'] as $key => $reader) {
            if ($maxValues['countCheckOuts'] < $reader['countCheckOuts']) {
                $maxValues['countCheckOuts'] = $reader['countCheckOuts'];
                $data['topReader'] = $key;
            }

            if ($maxValues['countBooks'] < $reader['countBooks']) {
                $maxValues['countBooks'] = $reader['countBooks'];
                $data['maxBooks'] = $key;
            }
        }

        foreach ($preparedData['books'] as $key => $book) {
            if ($maxValues['timeInCheckOut'] < abs($book['timeInCheckOut'])) {
                $maxValues['timeInCheckOut'] = abs($book['timeInCheckOut']);
                $data['maxTimeInCheckOut'] = $key;
            }

            if ($book['checkOut']) {
                $data['checkOutBooksCount']++;
            }
        }
        return [
            'чаще других брал книги' => $data['topReader'],
            'дольше всех отсутствовала в библиотеке' => $data['maxTimeInCheckOut'],
            'книг отсутствует в библиотеке на текущий момент' => $data['checkOutBooksCount'],
            'на данный момент имеет больше всего книг на руках' => $data['maxBooks'],
        ];
    }

    public function getReport($format)
    {
        $data = $this->getData();
        switch ($format) {
            case self::JSON_FORMAT:
                return json_encode($data);
                break;
            case self::TEXT_FORMAT:
                return serialize($data);
                break;
            default:
                die('Unknown format');
                break;
        }
    }
}