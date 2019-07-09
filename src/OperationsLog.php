<?php


namespace RGPSTT;


use DateTime;

class OperationsLog
{
    protected $log_data = [];
    protected $configuration = [];
    protected $c_utils;

    public function __construct(array $log_data, Configuration $configuration)
    {
        $this->log_data = array_map(function ($d, $i) {
            $d['idx'] = $i;
            return new Operation($d);
        }, $log_data, array_keys($log_data));

        $this->configuration = $configuration;

        $this->c_utils = new CurrencyUtils($this->configuration['currencies']);
    }

    public function calculateFee(Operation $operation): float
    {

        switch ($operation->getOpType()) {
            case 'cash_in':
                $service_charge = $operation->getAmount() * $this->configuration['cash_in_fee'];

                return min($this->c_utils->convertToDefaultCurrency($service_charge, $operation->getCurrency()),
                    $this->configuration['cash_in_max_fee']);
                break;
            case 'cash_out':
                switch ($operation->getUserType()) {
                    case 'natural':
                        $user_ops = $this->getWeeklyUserOps($operation->getUserId(), $operation->getOpType(),
                            $operation->getDate(), $operation->getIdx());


                        if ($user_ops['ops'] > $this->configuration['cash_out_limit_ops']) {
                            return $operation->getAmount() * $this->configuration['cash_out_fee_natural'];
                        } else {
                            if ($user_ops['amount'] <= $this->configuration['cash_out_limit_amount']) {

                                return 0;
                            } elseif ($user_ops['amount'] > $this->configuration['cash_out_limit_amount']) {

                                return $this->c_utils->convertFromDefaultCurrency($user_ops['amount'] - $this->configuration['cash_out_limit_amount'],
                                        $operation->getCurrency()) * $this->configuration['cash_out_fee_natural'];

                            }
                        }


                        break;
                    case 'legal':
                        $service_charge = $operation->getAmount() * $this->configuration['cash_out_fee_legal'];

                        return max($this->c_utils->convertToDefaultCurrency($service_charge,
                            $operation->getCurrency()), $this->configuration['cash_out_min_fee_legal']);
                        break;
                }
                break;
        }
    }

    protected function getWeeklyUserOps(int $user_id, string $type, DateTime $date, int $index): array
    {

        $ops = array_filter($this->log_data, function (Operation $item) use ($user_id, $type, $date, $index) {
            return $item->getUserId() === $user_id && $item->getOpType() === $type && $item->getDate()->format('oW') === $date->format('oW') && $item->getIdx() <= $index;
        });


        return array_reduce($ops, function ($carry, Operation $op) {
            if ($carry['amount'] > $this->configuration['cash_out_limit_amount']) {
                $carry['amount'] = $this->configuration['cash_out_limit_amount'];
            }

            $carry['amount'] += $this->c_utils->convertToDefaultCurrency($op->getAmount(), $op->getCurrency());

            $carry['ops'] += 1;

            return $carry;
        }, [
            'amount' => 0,
            'ops' => 0,
        ]);

    }

    /**
     * @return array
     */
    public function getLogData(): array
    {
        return $this->log_data;
    }


}
