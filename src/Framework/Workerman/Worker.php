<?php
namespace Impress\Framework\Workerman;
class Worker extends \Workerman\Worker
{
    public static $AppName = null;
    const FILE_TYPE_LOG = 0;
    const FILE_TYPE_PID = 1;
    const FILE_TYPE_STDOUT = 2;

    public static function runAll($AppName = null)
    {
        self::$AppName = $AppName ?: "workerman";

        static::$logFile = self::workermanTempFile(self::FILE_TYPE_LOG);
        static::$pidFile = self::workermanTempFile(self::FILE_TYPE_PID);
        static::$stdoutFile = self::workermanTempFile(self::FILE_TYPE_STDOUT);
        parent::runAll();
    }

    private static function workermanTempFile($type)
    {
        $filename = null;
        switch ($type) {
            case self::FILE_TYPE_PID:
                $filename = storage_path('pid') . DIRECTORY_SEPARATOR . "workerman" . DIRECTORY_SEPARATOR . self::$AppName . ".pid";
                break;
            case self::FILE_TYPE_STDOUT:
                $filename = storage_path('logs') . DIRECTORY_SEPARATOR . "workerman" . DIRECTORY_SEPARATOR . self::$AppName . ".stdout.log";
                break;
            case self::FILE_TYPE_LOG:
            default:
                $filename = storage_path('logs') . DIRECTORY_SEPARATOR . "workerman" . DIRECTORY_SEPARATOR . self::$AppName . ".log";
                break;
        }
        if (is_null($filename))
            return null;

        (is_dir(dirname($filename))) || mkdir(dirname($filename), 0777, true);
        return $filename;
    }
}
