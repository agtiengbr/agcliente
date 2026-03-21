<?php

class AgClienteLogger {
    
    protected static $logger;
    protected static $hash;
    
    public static function getHash()
    {
        if (!self::$hash) {
            self::$hash = uniqid();
        }

        return self::$hash;
    }
    public static function createLogger($filename, $level)
    {
        self::$logger = new FileLogger();
        self::$logger->setFilename($filename);
        self::$logger->level = $level;
    }
    
    public static function addLog($message, $severity = 1, $errorCode = null, $objectType = null, $objectId = null, $allowDuplicate = false, $idEmployee = null)
    {
        if (empty($idEmployee) && Validate::isLoadedObject(Context::getContext()->employee)) {
            $idEmployee = Context::getContext()->employee->id;
        }
        
        $logger = self::$logger;

        $hash = self::getHash();

        if (is_null($logger) || !method_exists($logger, 'logMessage')) {
            Logger::addLog("$hash - $message", $severity, $errorCode, $objectType, $objectId, $allowDuplicate, $idEmployee);
        } else {
            $logger->log("$hash - $message", $severity);
        }
    }
}
