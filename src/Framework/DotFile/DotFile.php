<?php
namespace Impress\Framework\DotFile;
class DotFile
{
    private static $contentPool = array();

    protected static function _get($baseDir, $parameters, $fileExtension = '.php')
    {
        $parameters = explode(".", $parameters);
        $file = $parameters[0];
        $fileName = $baseDir . DIRECTORY_SEPARATOR . $file . $fileExtension;
        if (!is_file($fileName)) {
            $fileName = dirname($baseDir) . DIRECTORY_SEPARATOR . $file . $fileExtension;
        }
        if (!is_file($fileName)) {
            throw new \RuntimeException("The file '{$fileName}' not found.");
        }
        $fileNameHash = md5($fileName);

        if (!isset(self::$contentPool[$fileNameHash])) {
            switch ($fileExtension) {
                default:
                    self::$contentPool[$fileNameHash] = require_once($fileName);
            }
        }

        if (count($parameters) === 1) {
            return self::$contentPool[$fileNameHash];
        }

        array_shift($parameters);
        $content = self::$contentPool[$fileNameHash];
        foreach ($parameters as $p) {
            $content = isset($content[$p]) ? $content[$p] : null;
        }
        return $content;
    }

    protected static function _value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}
