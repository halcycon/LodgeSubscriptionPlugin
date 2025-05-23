<?php

declare(strict_types=1);

namespace MauticPlugin\LodgeSubscriptionBundle\EventListener;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomTemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ScriptInjectionSubscriber implements EventSubscriberInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_TEMPLATE => ['onTemplateRender', 0],
        ];
    }

    /**
     * Inject custom scripts for integration page
     */
    public function onTemplateRender(CustomTemplateEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request) {
            return;
        }
        
        $uri = $request->getRequestUri();
        $route = $request->get('_route', '');
        
        // Only inject on our integration configuration page and only for plugin configuration
        // This is a very specific check to avoid affecting other pages
        if (strpos($uri, 'plugins/config/LodgeSubscription') !== false && 
            strpos($route, 'plugin_config') !== false) {
            $event->setTemplate('@LodgeSubscriptionBundle/Integration/inline_script.html.php');
        }
    }
} 