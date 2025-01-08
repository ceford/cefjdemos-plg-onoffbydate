<?php

/**
 * @package     Cefdemosonoffbydate.Console
 * @subpackage  Onoffbydate
 *
 * @copyright   Copyright (C) 2005 - 2021 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

namespace Cefjdemos\Plugin\System\Onoffbydate\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\Event\SubscriberInterface;
use Cefjdemos\Plugin\System\Onoffbydate\Console\OnoffbydateCommand;

final class Onoffbydate extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns the event this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
                \Joomla\Application\ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
        ];
    }

    /**
     * Returns the command class for the Onoffbydate CLI plugin.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function registerCommands(): void
    {
        $serviceId = 'onoffbydate.action';

        Factory::getContainer()->share(
            $serviceId,
            function (\Psr\Container\ContainerInterface $container) {
                // Do stuff to create command class and return it
                return new OnoffbydateCommand();
            },
            true
        );

        Factory::getContainer()
        ->get(\Joomla\CMS\Console\Loader\WritableLoaderInterface::class)
        ->add('onoffbydate:action', $serviceId);
    }
}
