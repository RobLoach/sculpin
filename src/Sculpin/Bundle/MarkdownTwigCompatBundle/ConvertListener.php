<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\MarkdownTwigCompatBundle;

use Sculpin\Bundle\MarkdownBundle\SculpinMarkdownBundle;
use Sculpin\Bundle\TwigBundle\SculpinTwigBundle;
use Sculpin\Core\Event\ConvertEvent;
use Sculpin\Core\Sculpin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sculpin Markdown/Twig Compatibility Bundle.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class ConvertListener implements EventSubscriberInterface
{
    /**
     * List of regular expresses needing placeholders
     *
     * @var array
     */
    protected static $addPlaceholderRe = array(
        '/^({%\s+(\w+).+?%})$/m',  // {% %} style code
        '/^({{.+?}})$/m',          // {{ }} style code
    );

    /**
     * Placeholder text
     *
     * @var string
     */
    protected static $placeholder = "\n<div><!-- sculpin-hidden -->$1<!-- /sculpin-hidden --></div>\n";

    /**
     * Regex used to remove placeholder
     *
     * @var unknown_type
     */
    protected static $removePlaceholderRe = '/(<div><!-- sculpin-hidden -->|<!-- \/sculpin-hidden --><\/div>)/m';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_BEFORE_CONVERT => 'beforeConvert',
            Sculpin::EVENT_AFTER_CONVERT => 'afterConvert',
        );
    }

    /**
     * Called before conversion
     *
     * @param ConvertEvent $convertEvent Convert Event
     */
    public function beforeConvert(ConvertEvent $convertEvent)
    {
        if ($convertEvent->isHandledBy(SculpinMarkdownBundle::CONVERTER_NAME, SculpinTwigBundle::FORMATTER_NAME)) {
            $content = $convertEvent->source()->content();
            foreach (self::$addPlaceholderRe as $re) {
                $content = preg_replace($re, self::$placeholder, $content);
            }
            $convertEvent->source()->setContent($content);
        }
    }

    /**
     * Called after conversion
     *
     * @param ConvertEvent $convertEvent Convert event
     */
    public function afterConvert(ConvertEvent $convertEvent)
    {
        if ($convertEvent->isHandledBy(SculpinMarkdownBundle::CONVERTER_NAME, SculpinTwigBundle::FORMATTER_NAME)) {
            $content = $convertEvent->source()->content();
            $content = preg_replace(self::$removePlaceholderRe, '', $content);
            $convertEvent->source()->setContent($content);
        }
    }
}
