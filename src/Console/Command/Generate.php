<?php

namespace Nails\SiteMap\Console\Command;

use Nails\Console\Command\Base;
use Nails\Factory;
use Nails\SiteMap\Constants;
use Nails\SiteMap\Service\SiteMap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Generate extends Base
{
    /**
     * Configures the command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('sitemap:generate')
            ->setDescription('Generates the sitemap');
    }

    // --------------------------------------------------------------------------

    /**
     * Executes the app
     *
     * @param InputInterface  $oInput  The Input Interface provided by Symfony
     * @param OutputInterface $oOutput The Output Interface provided by Symfony
     *
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput)
    {
        parent::execute($oInput, $oOutput);

        $oOutput->writeln('');
        $oOutput->writeln('<info>-----------------</info>');
        $oOutput->writeln('<info>SiteMap Generator</info>');
        $oOutput->writeln('<info>-----------------</info>');
        $oOutput->writeln('Beginning...');

        //  Writing
        /** @var SiteMap $oSiteMapService */
        $oSiteMapService = Factory::service('SiteMap', Constants::MODULE_SLUG);
        $oSiteMapService->write();

        //  Cleaning up
        $oOutput->writeln('');
        $oOutput->writeln('<comment>Cleaning up</comment>...');

        //  And we're done!
        $oOutput->writeln('');
        $oOutput->writeln('Complete!');

        return static::EXIT_CODE_SUCCESS;
    }
}
