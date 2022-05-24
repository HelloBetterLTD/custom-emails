<?php

namespace SilverStripers\CustomEmails\Dev;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripers\CustomEmails\Model\NotificationEmail;

class Injector
{

    use Configurable;
    use Injectable;

    /**
     * @var array
     * [
     *     'type' => [
     *          'name' => '',
     *          'dynamic' => '', # bool
     *          'arguments' => '',
     *          'template' => ''
     *     ]
     * ]
     */
    private static $definitions = [];

    public static function init_notifications()
    {
        $definitions = self::config()->get('definitions');
        $map = NotificationEmail::get()->map('ID', 'ID')->toArray();
        echo '<div><ul>';
        foreach ($definitions as $identifier => $data) {
            $notification = NotificationEmail::get()->find('Type', $identifier);
            if (!$notification) {
                $notification = NotificationEmail::create([
                    'Type' => $identifier
                ]);
                $notification->write();
            }
            echo sprintf('<li class="success">Initiated email for "%s"</li>', $identifier);
            unset($map[$notification->ID]);
        }

        // delete unused
        foreach ($map as $id) {
            if ($notification = NotificationEmail::get()->byID($id)) {
                $notification->write();
            }
        }
        echo '<li class="success">Completed init notification</li>';
        echo '</ul></div>';
    }

    public static function get_title_for_type($type)
    {
        $definitions = self::config()->get('definitions');
        if (!empty($definitions[$type])) {
            return $definitions[$type]['name'];
        }
        return 'Unknown';
    }

    public static function get_template_for_type($type)
    {
        $definitions = self::config()->get('definitions');
        if (!empty($definitions[$type]) && !empty($definitions[$type]['template'])) {
            return $definitions[$type]['template'];
        }
        return null;
    }

    public static function get_arguments_for_type($type)
    {
        $definitions = self::config()->get('definitions');
        if (!empty($definitions[$type])) {
            return $definitions[$type]['arguments'];
        }
        return [];
    }
    
    
    public static function is_dynamic($type)
    {
        $definitions = self::config()->get('definitions');
        if (!empty($definitions[$type])) {
            return !empty($definitions[$type]['dynamic']) && $definitions[$type]['dynamic'];
        }
        return [];
    }

}
