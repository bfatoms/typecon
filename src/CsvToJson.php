<?php

namespace BfAtoms\Typecon;

class CsvToJson
{

    protected $conversion = [
        'extension' => 'json',
        'type' => 'application/json',
        'options' => 0,
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
        'join' => '_',
        'numbers' => 'strings'
    ];

    protected $data;


    public function convert(): string
    {
        $data = $this->parseData();
        $keys = $this->parseCsv(array_shift($data));
        $splitKeys = array_map(function ($key) {
            return explode($this->conversion['join'], $key);
        }, $keys);
        return json_encode(array_map(function ($line) use ($splitKeys) {
            return $this->getJsonObject($line, $splitKeys);
        }, $data), $this->conversion['options']);
    }


    private function getJsonObject($line, $splitKeys, array $jsonObject = []): array
    {
        $values = $this->parseCsv($line);
        for ($valueIndex = 0, $count = \count($values); $valueIndex < $count; $valueIndex++) {
            if ($values[$valueIndex] === '') {
                continue;
            }
            $this->setJsonValue($splitKeys[$valueIndex], 0, $jsonObject, $values[$valueIndex]);
        }
        return $jsonObject;
    }


    private function setJsonValue($splitKey, $splitKeyIndex, &$jsonObject, $value): void
    {
        $keyPart = $splitKey[$splitKeyIndex];
        if (\count($splitKey) > $splitKeyIndex + 1) {
            if (!array_key_exists($keyPart, $jsonObject)) {
                $jsonObject[$keyPart] = [];
            }
            $this->setJsonValue($splitKey, $splitKeyIndex+1, $jsonObject[$keyPart], $value);
        } else {
            if (is_numeric($value) && $this->conversion['numbers'] === 'numbers') {
                $value = 0 + $value;
            }
            $jsonObject[$keyPart] = $value;
        }
    }

    private function parseCsv($line): array
    {
        return str_getcsv(
            $line,
            $this->conversion['delimiter'],
            $this->conversion['enclosure'],
            $this->conversion['escape']
        );
    }

    private function parseData(): array
    {
        $data = explode("\n", $this->data);
        if (end($data) === '') {
            array_pop($data);
        }
        return $data;
    }


    public function setConversionKey($key, $value): array
    {
        $this->conversion[$key] = $value;
        return $this->conversion;
    }

    public function filepath($filepath)
    {
        $this->data = file_get_contents($filepath);
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
