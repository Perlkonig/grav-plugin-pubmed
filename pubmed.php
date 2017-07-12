<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class PubmedPlugin
 * @package Grav\Plugin
 */
class PubmedPlugin extends Plugin
{
    private $pubmed = null;

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onShortcodeHandlers' => ['onShortcodeHandlers', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
        ];
    }

    public function onShortcodeHandlers(Event $e)
    {
        $this->grav['shortcode']->registerShortcode('PubmedShortcode.php', __DIR__);
    }

    public function onTwigSiteVariables(Event $e)
    {
        $this->grav['assets']
            ->add('theme://assets/pubmed.css');
    }
}
