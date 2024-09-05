<?php

namespace SilverStripers\CustomEmails\Admin;

use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripers\CustomEmails\Model\NotificationEmail;

class NotificationsItemRequest extends GridFieldDetailForm_ItemRequest
{

    private static $allowed_actions = [
        'ItemEditForm'
    ];

    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();
        $actions = $form->Actions();
        if (SiteConfig::current_site_config()->SendTestEmailsTo && $this->record->ID) {
            /* @var $majorActions CompositeField */
            $majorActions = $actions->fieldByName('MajorActions');
            $majorActions->push(
                FormAction::create('sendTestEmail', 'Send a test')
                    ->addExtraClass('btn btn-outline-primary')
            );
        }
        return $form;
    }

    public function sendTestEmail($data, $form)
    {
        /* @var $notification NotificationEmail */
        $notification = $this->record;
        $arguments = $notification->getArguments();
        $params = [];
        foreach ($arguments as $name) {
            $params[$name] = DBHTMLText::create()
                ->setValue(sprintf('<span style="border: 1px solid red;">%s value</span>', $name)); //self::get_random_value();
        }
        $processor = NotificationEmail::get_processor(
            $notification->Type,
            SiteConfig::current_site_config()->SendTestEmailsTo,
            null,
            $params
        );
        $processor->send();

        $form->sessionMessage('Test email is sent', 'good', ValidationResult::CAST_HTML);
        $controller = $this->getToplevelController();
        return $this->edit($controller->getRequest());
    }

    public function get_random_value($length = 6)
    {
        $string = '';
        $vowels = ['a','e','i','o','u'];
        $consonants = [
            'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
            'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
        ];
        $max = $length / 2;
        for ($i = 1; $i <= $max; $i++)
        {
            $string .= $consonants[rand(0,19)];
            $string .= $vowels[rand(0,4)];
        }
        return $string;
    }

}
