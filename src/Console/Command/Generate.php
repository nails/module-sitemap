<?php

namespace Nails\SiteMAp\Console\Command;

use Nails\Console\Command\Base;
use Nails\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Generate extends Base
{
    /**
     * Configures the app
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
     * @param  InputInterface  $oInput  The Input Interface provided by Symfony
     * @param  OutputInterface $oOutput The Output Interface provided by Symfony
     *
     * @throws \Exception
     * @return void
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
        $oSiteMapService = Factory::service('SiteMap', 'nails/module-sitemap');
        $oSiteMapService->write();

        //  Cleaning up
        $oOutput->writeln('');
        $oOutput->writeln('<comment>Cleaning up...</comment>');

        //  And we're done!
        $oOutput->writeln('');
        $oOutput->writeln('Complete!');
    }
}
