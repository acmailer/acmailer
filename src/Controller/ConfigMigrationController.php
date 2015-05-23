<?php
namespace AcMailer\Controller;

use AcMailer\Service\ConfigMigrationServiceInterface;
use Zend\Console\ColorInterface;
use Zend\Console\Request;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Config\Writer\WriterInterface;
use AcMailer\Exception\InvalidArgumentException;

/**
 * Class ConfigMigrationController
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class ConfigMigrationController extends AbstractConsoleController
{
    const WRITER_NAMESPACE = '\Zend\Config\Writer';

    private $formats = [
        'php' => 'PhpArray',
        'ini' => 'Ini',
        'json' => 'Json',
        'xml' => 'Xml'
    ];

    /**
     * @var ConfigMigrationServiceInterface
     */
    protected $configMigrationService;
    /**
     * @var array
     */
    protected $globalConfig;

    public function __construct(ConfigMigrationServiceInterface $configMigrationService, array $globalConfig)
    {
        $this->configMigrationService = $configMigrationService;
        $this->globalConfig = $globalConfig;
    }

    public function parseConfigAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $format = $request->getParam('format', 'php');
        $writer = $this->createWriter($format);

        $configKey = $request->getParam('configKey', 'mail_options'); // Default to the old config key
        $outputFile = $request->getParam('outputFile');

        // Global configuration not found
        if (! isset($this->globalConfig[$configKey])) {
            $this->getConsole()->writeLine(
                sprintf('It wasn\'t possible to find the configuration key "%s"', $configKey),
                ColorInterface::RED
            );
            return PHP_EOL;
        }

        // Write new configuration to CLI
        $newConfig = $this->configMigrationService->parseConfig($this->globalConfig[$configKey]);
        if (! isset($outputFile)) {
            $this->getConsole()->writeLine(
                'This is your new configuration for the AcMailer module:',
                ColorInterface::GREEN
            );
            $this->getConsole()->write($writer->toString($newConfig), ColorInterface::LIGHT_BLUE);
            return PHP_EOL;
        }

        // Write new configuration to file
        $writer->toFile($outputFile, $newConfig);
        $this->getConsole()->writeLine(
            sprintf('The new configuration for the AcMailer module has been written to "%s":', $outputFile),
            ColorInterface::GREEN
        );
        return PHP_EOL;
    }

    /**
     * @param $format
     * @return WriterInterface
     */
    protected function createWriter($format)
    {
        if (! array_key_exists($format, $this->formats) && ! in_array($format, $this->formats)) {
            throw new InvalidArgumentException(sprintf(
                'Provided format "%s" is not valid. Expected one of ["%s"]',
                $format,
                implode('", "', array_keys($this->formats))
            ));
        }

        $writerClass = array_key_exists($format, $this->formats) ? $this->formats[$format] : $format;
        $writerClass = sprintf('%s\%s', self::WRITER_NAMESPACE, $writerClass);
        return new $writerClass;
    }
}
