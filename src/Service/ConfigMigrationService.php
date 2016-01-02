<?php
namespace AcMailer\Service;

/**
 * Class ConfigMigrationService
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class ConfigMigrationService implements ConfigMigrationServiceInterface
{
    /**
     * Reads a configuration array with the structure used in version 4.5 and earlier and parses it to the new one
     *
     * @param array $oldConfig
     * @return array
     */
    public function parseConfig(array $oldConfig)
    {
        $newConfig = [
            'message_options' => [],
            'smtp_options' => [],
            'file_options' => []
        ];

        // Mail acapter
        if (isset($oldConfig['mail_adapter_service'])) {
            $newConfig['mail_adapter'] = $oldConfig['mail_adapter_service'];
        } elseif (isset($oldConfig['mail_adapter'])) {
            $newConfig['mail_adapter'] = $oldConfig['mail_adapter'];
        }

        // Common message options
        if (isset($oldConfig['from'])) {
            $newConfig['message_options']['from'] = $oldConfig['from'];
        }
        if (isset($oldConfig['from_name'])) {
            $newConfig['message_options']['from_name'] = $oldConfig['from_name'];
        }
        if (isset($oldConfig['to'])) {
            $newConfig['message_options']['to'] = $oldConfig['to'];
        }
        if (isset($oldConfig['cc'])) {
            $newConfig['message_options']['cc'] = $oldConfig['cc'];
        }
        if (isset($oldConfig['bcc'])) {
            $newConfig['message_options']['bcc'] = $oldConfig['bcc'];
        }
        if (isset($oldConfig['subject'])) {
            $newConfig['message_options']['subject'] = $oldConfig['subject'];
        }

        // Body
        $newConfig['message_options']['body'] = [];
        if (isset($oldConfig['body'])) {
            $newConfig['message_options']['body']['content'] = $oldConfig['body'];
        }
        if (isset($oldConfig['body_charset'])) {
            $newConfig['message_options']['body']['charset'] = $oldConfig['body_charset'];
        }
        if (isset($oldConfig['template'])) {
            $newConfig['message_options']['body']['template'] = $oldConfig['template'];
            $newConfig['message_options']['body']['use_template'] = $oldConfig['template']['use_template'];
            unset($newConfig['message_options']['body']['template']['use_template']);
            $newConfig['message_options']['body']['template']['default_layout'] = [];
        }

        // Attachments
        if (isset($oldConfig['attachments'])) {
            $newConfig['message_options']['attachments'] = $oldConfig['attachments'];
        }

        // SMTP
        if (isset($oldConfig['server'])) {
            $newConfig['smtp_options']['host'] = $oldConfig['server'];
        }
        if (isset($oldConfig['port'])) {
            $newConfig['smtp_options']['port'] = $oldConfig['port'];
        }
        if (isset($oldConfig['connection_class'])) {
            $newConfig['smtp_options']['connection_class'] = $oldConfig['connection_class'];
        }
        $newConfig['smtp_options']['connection_config'] = [];
        if (isset($oldConfig['smtp_user'])) {
            $newConfig['smtp_options']['connection_config']['username'] = $oldConfig['smtp_user'];
        }
        if (isset($oldConfig['smtp_user'])) {
            $newConfig['smtp_options']['connection_config']['username'] = $oldConfig['smtp_user'];
        }
        if (isset($oldConfig['smtp_password'])) {
            $newConfig['smtp_options']['connection_config']['password'] = $oldConfig['smtp_password'];
        }
        if (isset($oldConfig['ssl'])) {
            $newConfig['smtp_options']['connection_config']['ssl'] = $oldConfig['ssl'];
        }

        // File
        if (isset($oldConfig['file_path'])) {
            $newConfig['file_options']['path'] = $oldConfig['file_path'];
        }
        if (isset($oldConfig['file_callback'])) {
            $newConfig['file_options']['callback'] = $oldConfig['file_callback'];
        }

        return [
            'acmailer_options' => [
                'default' => $newConfig
            ]
        ];
    }
}
