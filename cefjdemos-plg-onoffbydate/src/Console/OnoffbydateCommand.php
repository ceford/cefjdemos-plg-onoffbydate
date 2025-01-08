<?php

/**
 * @package     Cefdemosonoffbydate.Console
 * @subpackage  Onoffbydate
 *
 * @copyright   Copyright (C) 2025 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

namespace Cefjdemos\Plugin\Console\Onoffbydate\Console;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\Database\DatabaseAwareTrait;

class OnoffbydateCommand extends AbstractCommand
{
    use DatabaseAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected static $defaultName = 'onoffbydate:action';

    /**
     * @var InputInterface
     * @since version
     */
    private $cliInput;

    /**
     * SymfonyStyle Object
     * @var SymfonyStyle
     * @since 4.0.0
     */
    private $ioStyle;

    /**
     * The params associated with the plugin, plus getter and setter
     * These are injected into this class by the plugin instance
     */
    protected $params;

    protected function getParams()
    {
        return $this->params;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $this->addArgument(
            'season',
            InputArgument::REQUIRED,
            'winter or summer or weekday or weekend'
        );
        $this->addArgument(
            'action',
            InputArgument::REQUIRED,
            'publish or unpublish'
        );

        $this->addArgument(
            'module_id',
            InputArgument::REQUIRED,
            'module id'
        );

        $help = "<info>%command.name%</info> Toggles module Enabled/Disabled state\n";
        $help .= "Usage: <info>php %command.full_name% season action module_id where\n";
        $help .= "    season is one of winter or summer or weekday or weekend,\n";
        $help .= "    action is one of publish or unpublish and\n";
        $help .= "    module_id is the id of the module to publish or unpublish.</info>\n";

        $this->setDescription('Called by a cron job to set the enabled state of a module.');
        $this->setHelp($help);
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureIO($input, $output);

        $season = $this->cliInput->getArgument('season');
        $action = $this->cliInput->getArgument('action');
        $module_id = $this->cliInput->getArgument('module_id');

        switch ($season) {
            case 'winter':
                $result = $this->winter($module_id, $action);
                break;
            case 'weekend':
                $result = $this->weekend($module_id, $action);
                break;
            default:
                $this->ioStyle->error("Unknown season: {$season}");
                return 0;
        }

        return 1;
    }

    protected function publish($module_id, $published)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        //$db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->update('#__modules')
            ->set('published = ' . $published)
            ->where('id = ' . $module_id);
        $db->setQuery($query);
        $db->execute();
    }

    protected function weekend($module_id, $action)
    {
        // get the day of the week
        $day = date('w');
        if (in_array($day, array(0,6))) {
            $msg = "Today is a weekend.";
        } else {
            $msg = "Today is not a weekend.";
        }
        $published = $action === 'publish' ? 1 : 0;

        $this->publish($module_id, $published);

        $state = empty($published) ? 'Unpublished' : 'Published';

        $this->ioStyle->success("That seemed to work. {$msg} Module {$module_id} has been {$state}");
    }

    protected function winter($module_id, $action)
    {
        // get the month of the month
        $month = date('n');
        if (in_array($month, array(1,2,11,12))) {
            $msg = "Today is in winter.";
        } else {
            $msg = "Today is not in winter.";
        }
        $published = $action === 'publish' ? 1 : 0;

        $this->publish($module_id, $published);

        $state = empty($published) ? 'Unpublished' : 'Published';

        $this->ioStyle->success("That seemed to work. {$msg} Module {$module_id} has been {$state}");
    }

    /**
     * Configures the IO
     *
     * @param   InputInterface   $input   Console Input
     * @param   OutputInterface  $output  Console Output
     *
     * @return void
     *
     * @since 4.0.0
     *
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->cliInput = $input;
        $this->ioStyle = new SymfonyStyle($input, $output);
    }
}
