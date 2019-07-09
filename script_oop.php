<?php
require __DIR__ . '/vendor/autoload.php';

use RGPSTT\Configuration;
use RGPSTT\CurrencyUtils;
use RGPSTT\DataLoader;
use RGPSTT\OperationsLog;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

(new Application('fees', '1.0.0'))
    ->register('fees')
    ->addArgument('csv', InputArgument::REQUIRED, 'CSV file')
    ->addArgument('config', InputArgument::OPTIONAL, 'YAML config file', 'config.yml')
    ->setCode(function (InputInterface $input, OutputInterface $output) {

        $csv_path = $input->getArgument('csv');

        $config_path = $input->getArgument('config');

        $configuration = new Configuration($config_path);

        $loader = new DataLoader($csv_path, $configuration['csv_field_config']);

        $c_utils = new CurrencyUtils($configuration['currencies']);

        $log_data = $loader->getRawData();

        $oplog = new OperationsLog($log_data, $configuration);

        foreach ($oplog->getLogData() as $operation) {
            try {
                $output->writeln($c_utils->roundCurrency($oplog->calculateFee($operation), $operation->getCurrency()));
            } catch (Exception $ex) {
                continue;
            }

        }

    })
    ->getApplication()
    ->run();
