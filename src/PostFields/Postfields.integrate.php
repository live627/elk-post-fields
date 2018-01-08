<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */
class Postfields_Integrate
{
    /**
     * Register hooks to the system
     *
     * @return array
     */
    public static function register()
    {
        // $hook, $function, $file
        return [
            ['integrate_prepare_display_context', '\\ElkArte\\addons\\PostFields\\Integration::displayFields'],
            ['integrate_display_message_list', '\\ElkArte\\addons\\PostFields\\Integration::fetchFields'],
        ];
    }

    /**
     * Register ACP config hooks for setting values
     *
     * @return array
     */
    public static function settingsRegister()
    {
        // $hook, $function, $file
        return [
            ['integrate_admin_areas', '\\ElkArte\\addons\\PostFields\\Integration::admin_areas'],
        ];
    }

    /**
     */
    public static function setting_callback($value)
    {
        if ($value) {
            enableModules('postfields', ['post', 'display']);
            Hooks::instance()->enableIntegration(self::class);
        } else {
            disableModules('postfields', ['post', 'display']);
            Hooks::instance()->disableIntegration(self::class);
        }
    }
}