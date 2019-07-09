<?php


namespace RGPSTT;


use DateTime;
use InvalidArgumentException;

class Operation
{
    protected $date;
    protected $user_id;
    protected $user_type;
    protected $op_type;
    protected $amount;
    protected $currency;
    protected $idx;

    public function __construct(array $operation_data)
    {
        $operation_data = $this->validate($operation_data);
        $this
            ->setDate($operation_data['date'])
            ->setUserId($operation_data['user_id'])
            ->setUserType($operation_data['user_type'])
            ->setOpType($operation_data['op_type'])
            ->setAmount($operation_data['amount'])
            ->setCurrency($operation_data['currency'])
            ->setIdx($operation_data['idx']);
    }

    /**
     * @param array $row
     * @return array
     */
    public function validate(array $row): array
    {
        return filter_var_array($row, [
            'date' => [
                'filter' => FILTER_CALLBACK,
                'options' => [$this, 'validateDate']
            ],
            'user_id' => FILTER_VALIDATE_INT,
            'user_type' => [
                'filter' => FILTER_CALLBACK,
                'options' => [$this, 'validateUserType']
            ],
            'op_type' => [
                'filter' => FILTER_CALLBACK,
                'options' => [$this, 'validateOp']
            ],
            'amount' => FILTER_VALIDATE_FLOAT,
            'currency' => true,
            'idx' => true,
        ], false);

    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     * @return Operation
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return Operation
     */
    public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserType(): string
    {
        return $this->user_type;
    }

    /**
     * @param string $user_type
     * @return Operation
     */
    public function setUserType($user_type)
    {
        $this->user_type = $user_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getOpType(): string
    {
        return $this->op_type;
    }

    /**
     * @param string $op_type
     * @return Operation
     */
    public function setOpType($op_type)
    {
        $this->op_type = $op_type;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return Operation
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Operation
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return int
     */
    public function getIdx(): int
    {
        return $this->idx;
    }

    /**
     * @param int $idx
     * @return Operation
     */
    public function setIdx($idx)
    {
        $this->idx = $idx;
        return $this;
    }

    protected function validateDate($raw_date)
    {
        $date = DateTime::createFromFormat('Y-m-d', $raw_date);

        if ($date === false) {
            throw new InvalidArgumentException('Not a valid date in date field');
        }

        return $date;
    }

    protected function validateUserType($raw_type)
    {
        $valid_type = in_array($raw_type, ['natural', 'legal']);

        if ($valid_type === false) {
            throw new InvalidArgumentException("Invalid type '{$raw_type}' found in user type field");
        }

        return $raw_type;
    }

    protected function validateOp($op)
    {
        $valid_op = in_array($op, ['cash_in', 'cash_out']);

        if ($valid_op === false) {
            throw new InvalidArgumentException("Invalid '{$op}' found in operation type field");
        }

        return $op;
    }


}
