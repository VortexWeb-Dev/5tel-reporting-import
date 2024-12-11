<?php

class Logger {
    private $logFile;
    private $logLevel;

    // Constructor to initialize log file path and log level
    public function __construct($logFile = 'error_log.txt', $logLevel = 'error') {
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
    }

    // Log error messages
    public function logError($message) {
        if ($this->shouldLog('error')) {
            $this->writeLog('ERROR', $message);
        }
    }

    // Log info messages
    public function logInfo($message) {
        if ($this->shouldLog('info')) {
            $this->writeLog('INFO', $message);
        }
    }

    // Log warning messages
    public function logWarning($message) {
        if ($this->shouldLog('warning')) {
            $this->writeLog('WARNING', $message);
        }
    }

    // Write log message to the file
    private function writeLog($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] {$level}: {$message}\n";
        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
    }

    // Check if the log level should be logged
    private function shouldLog($level) {
        $levels = ['info', 'warning', 'error'];
        $levelPriority = array_flip($levels);

        return $levelPriority[$level] >= $levelPriority[$this->logLevel];
    }
}
