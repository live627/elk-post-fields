<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */
class Postfields_Display_Module implements ElkArte\sources\modules\Module_Interface
{
    /**
     * {@inheritdoc}
     */
    public static function hooks(\Event_Manager $eventsManager)
    {
        return [
            ['topicinfo', [self::class, 'topicinfo']],
        ];
    }

    public function topicinfo($topicinfo)
    {
    }
}