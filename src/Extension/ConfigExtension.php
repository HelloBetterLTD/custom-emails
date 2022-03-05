<?php

namespace SilverStripers\CustomEmails\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\ORM\DataExtension;
use SilverStripers\CustomEmails\Model\NotificationEmail;

class ConfigExtension extends DataExtension
{

    public function updateCMSFields(FieldList $fields) : void
    {
        $fields->addFieldToTab(
            'Root.Notifications',
            GridField::create('Notifications')
                ->setList(NotificationEmail::get())
                ->setConfig(
                    GridFieldConfig_RecordEditor::create()
                        ->removeComponentsByType(GridFieldAddNewButton::class)
                )
        );
    }

}
