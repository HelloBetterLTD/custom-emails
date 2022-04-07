<?php

namespace SilverStripers\CustomEmails\Model;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripers\CustomEmails\Dev\Injector;
use SilverStripers\CustomEmails\Transport\Processor;

class NotificationEmail extends DataObject implements PermissionProvider
{

    const UPDATE_EMAIL_NOTIFICATIONS = 'UPDATE_EMAIL_NOTIFICATIONS';

    private static $db = [
        'Type' => 'Varchar',
        'FromEmail' => 'Varchar',
        'ToEmail' => 'Text',
        'ReplyToEmail' => 'Varchar',
        'CCEmail' => 'Varchar',
        'BCCEmail' => 'Varchar',
        'Subject' => 'Varchar',
        'Body' => 'HTMLText',
    ];

    private static $many_many = [
        'Attachments' => File::class
    ];

    private static $table_name = 'NotificationEmail';

    private static $summary_fields = [
        'Title',
        'Subject',
    ];

    public function getCMSFields() : FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeByName([
            'Type',
            'FromEmail',
            'ToEmail',
            'ReplyToEmail',
            'CCEmail',
            'BCCEmail',
            'Attachments'
        ]);

        /* @var $body HTMLEditorField */
        $body = $fields->dataFieldByName('Body')
            ->setRows(8)
            ->setEditorConfig(TinyMCEConfig::get('email_body'));
        $arguments = $this->getArguments();
        if (!empty($arguments)) {
            $description = '{$' . implode('}, {$', $arguments) . '}';
            $body->setDescription(sprintf('Following variables can be used:<br>%s', $description));
        }
        $fields->addFieldToTab(
            'Root.Main',
            UploadField::create('Attachments')
        );

        $fields->addFieldsToTab('Root.Settings', [
            TextField::create('FromEmail', 'From email')
                ->setDescription('Leave blank if you are happy to send emails with the default email address'),
            TextField::create('ToEmail', 'To email')
                ->setDescription('Leave blank for dynamic emails. For multiple email enter a comma separated text'),
            TextField::create('ReplyToEmail', 'Reply to email'),
            TextField::create('CCEmail', 'CC email')
                ->setDescription('Comma separate multiple emails'),
            TextField::create('BCCEmail', 'BCC email')
                ->setDescription('Comma separate multiple emails'),
        ]);
        return $fields;
    }

    public static function get_processor($type, $to = null, $from = null, $data = []) : Processor
    {
        $processor = Processor::create($type);
        if ($to) {
            $processor->setTo($to);
        }
        if ($from) {
            $processor->setFrom($from);
        }
        if ($data) {
            $processor->setData($data);
        }
        return $processor;
    }

    public function providePermissions() : array
    {
        return [
            self::UPDATE_EMAIL_NOTIFICATIONS => [
                'name' => 'Update email notifications',
                'category' => 'Notifications'
            ]
        ];
    }

    public function canView($member = null) : bool
    {
        return Permission::check(self::UPDATE_EMAIL_NOTIFICATIONS);
    }

    public function canEdit($member = null) : bool
    {
        return Permission::check(self::UPDATE_EMAIL_NOTIFICATIONS);
    }

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function onAfterBuild()
    {
        Injector::init_notifications();
    }

    public function getTitle()
    {
        return Injector::get_title_for_type($this->Type);
    }

    public function getArguments()
    {
        return Injector::get_arguments_for_type($this->Type);
    }

    public function getTemplate()
    {
        return Injector::get_template_for_type($this->Type);
    }


}
