<?php


namespace RGPSTT;


use InvalidArgumentException;
use LengthException;
use RuntimeException;

class DataLoader
{

    protected $data_array = [];

    public function __construct(string $file_path, array $field_config)
    {
        if (!is_readable($file_path)) {
            throw new RuntimeException('File not found');
        }

        if (empty($field_config)) {
            throw new InvalidArgumentException('Empty field configuration');
        }

        $csv_lines = file($file_path);

        $csv_lines = array_filter($csv_lines);

        asort($field_config);

        $this->data_array = array_map(function ($line) use ($field_config) {
            $raw_csv = str_getcsv($line);

            if (count($raw_csv) < count($field_config)) {
                throw new LengthException('The data file does not contain all required fields defined in the configuration');
            }

            $keys = array_keys($field_config);
            $values = array_intersect_key($raw_csv, array_fill_keys(array_values($field_config), true));

            if (count($keys) !== count($values)) {
                throw new LengthException("There's an error in the field configuration");
            }

            return array_combine($keys, $values);

        }, $csv_lines);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data_array;
    }

    protected function validate()
    {

    }
}
