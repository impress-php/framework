<?php
namespace Impress\Framework\ORM\Doctrine;

use Doctrine\ORM\Tools\Setup as DoctrineSetup;

class Setup
{
    public static function createAnnotationMetadataConfiguration()
    {
        $isDevMode = config("database.doctrine.isDevMode", false);
        $paths = config("database.doctrine.paths", [
            app_path("Model") . DIRECTORY_SEPARATOR . "doctrineEntities"
        ]);
        if (!$paths) {
            throw new \RuntimeException("Invalid paths: " . $paths);
        }

        return DoctrineSetup::createAnnotationMetadataConfiguration($paths, $isDevMode);
    }
}
