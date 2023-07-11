<?php

namespace SilverStripers\CustomEmails\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripers\CustomEmails\Admin\NotificationsItemRequest;
use SilverStripers\CustomEmails\Model\NotificationEmail;
use SilverStripers\GridSwitch\Field\SwitchField;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

class ConfigExtension extends DataExtension
{

    private static $db = [
        'SendTestEmailsTo' => 'Varchar'
    ];

    public function updateCMSFields(FieldList $fields) : void
    {
        $fields->addFieldsToTab(
            'Root.Notifications',
            [
                GridField::create('Notifications')
                    ->setList(NotificationEmail::get())
                    ->setConfig(
                        $config = GridFieldConfig_RecordEditor::create()
                            ->removeComponentsByType(GridFieldAddNewButton::class)
                    ),
                TextField::create('SendTestEmailsTo', 'Send test emails to')
                    ->setDescription('This is the email thats used to send test emails.')
            ]
        );

        $config->removeComponentsByType(GridFieldDataColumns::class);

        /* @var $detailForm GridFieldDetailForm */
        $detailForm = $config->getComponentByType(GridFieldDetailForm::class);
        $detailForm->setItemRequestClass(NotificationsItemRequest::class);

        /* @var $columns GridFieldDataColumns */
        $columns = GridFieldEditableColumns::create();
        $columns->setDisplayFields([
            'Enabled' => function ($record, $columnName, $gridField) {
                if ($record) {
                    $field = SwitchField::create(
                        'Enabled',
                        ''
                    );
                    $field->setOn($record->Enabled);
                    return $field;
                }
            },
            'Title' => 'Title',
            'Subject' => 'Subject'
        ]);

        $config->addComponent($columns, GridFieldEditButton::create());
    }

}
