<?php
require __DIR__ . '/vendor/autoload.php';

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

        $configuration = new \RGPSTT\Configuration($config_path);

        $loader = new \RGPSTT\DataLoader($csv_path, $configuration['csv_field_config']);

        $c_utils = new \RGPSTT\CurrencyUtils($configuration['currencies']);

        $user_ops = [];

        foreach ($loader->getData() as $operation) {

            switch ($operation['op_type']) {
                case 'cash_in':
                    $service_charge = $operation['amount'] * $configuration['cash_in_fee'];

                    $service_charge = min($c_utils->convertToDefaultCurrency($service_charge, $operation['currency']),
                        $configuration['cash_in_max_fee']);

                    break;
                case 'cash_out':
                    switch ($operation['user_type']) {
                        case 'natural':
                            $user_id = $operation['user_id'];
                            $date_id = date('oW', strtotime($operation['date']));

                            if (array_key_exists("$user_id-$date_id", $user_ops)) {
                                $user_ops["$user_id-$date_id"]['amount'] += $c_utils->convertToDefaultCurrency($operation['amount'],
                                    $operation['currency']);
                                $user_ops["$user_id-$date_id"]['ops'] += 1;
                            } else {
                                $user_ops["$user_id-$date_id"] = [
                                    'amount' => $c_utils->convertToDefaultCurrency($operation['amount'],
                                        $operation['currency']),
                                    'ops' => 1
                                ];
                            }

                            if ($user_ops["$user_id-$date_id"]['ops'] > $configuration['cash_out_limit_ops']) {
                                $service_charge = $operation['amount'] * $configuration['cash_out_fee_natural'];
                            } else {
                                if ($user_ops["$user_id-$date_id"]['amount'] <= $configuration['cash_out_limit_amount']) {

                                    $service_charge = 0;
                                } elseif ($user_ops["$user_id-$date_id"]['amount'] > $configuration['cash_out_limit_amount']) {

                                    $service_charge = $c_utils->convertFromDefaultCurrency($user_ops["$user_id-$date_id"]['amount'] - $configuration['cash_out_limit_amount'],
                                            $operation['currency']) * $configuration['cash_out_fee_natural'];

                                    $user_ops["$user_id-$date_id"]['amount'] = $configuration['cash_out_limit_amount'];
                                }
                            }

                            break;
                        case 'legal':
                            $service_charge = $operation['amount'] * $configuration['cash_out_fee_legal'];

                            $service_charge = max($c_utils->convertToDefaultCurrency($service_charge,
                                $operation['currency']), $configuration['cash_out_min_fee_legal']);
                            break;
                    }

                    break;
            }

            $output->writeln($c_utils->roundCurrency($service_charge, $operation['currency']));
        }

    })
    ->getApplication()
    ->setDefaultCommand('fees', true)
    ->run();
