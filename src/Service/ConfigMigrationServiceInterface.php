<?php
namespace AcMailer\Service;

/**
 * Interface ConfigMigrationServiceInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface ConfigMigrationServiceInterface
{
    /**
     * Reads a configuration array with the structure used in version 4.5 and earlier and parses it to the new one
     *
     * @param array $oldConfig
     * @return array
     */
    public function parseConfig(array $oldConfig);
}
