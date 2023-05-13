<?php

namespace SilverStripers\CustomEmails\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\ORM\DataExtension;
use SilverStripers\CustomEmails\Model\NotificationEmail;
use SilverStripers\GridSwitch\Field\SwitchField;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

class ConfigExtension extends DataExtension
{

    public function updateCMSFields(FieldList $fields) : void
    {
        $fields->addFieldToTab(
            'Root.Notifications',
            GridField::create('Notifications')
                ->setList(NotificationEmail::get())
                ->setConfig(
                    $config = GridFieldConfig_RecordEditor::create()
                        ->removeComponentsByType(GridFieldAddNewButton::class)
                )
        );

        $config->removeComponentsByType(GridFieldDataColumns::class);

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
