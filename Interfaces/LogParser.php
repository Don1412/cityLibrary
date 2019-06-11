<?php

namespace Interfaces;

interface LogParser
{
    public function __construct($file);
    public function getLogsArray();
}