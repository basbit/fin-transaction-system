<?php

namespace App\Shared\Infrastructure;

trait TraitEntityPropertyAccess
{
    public function toArray(bool $isRecursive = true): array
    {
        $result = [];

        foreach (get_object_vars($this) as $propertyName => $propertyValue) {
            if (is_null($propertyValue) || (is_array($propertyValue) && !$propertyValue)) {
                continue;
            }

            if ($isRecursive && is_object($propertyValue)) {
                $propertyValue = $propertyValue->toArray();
            }

            $result[$propertyName] = $propertyValue;
        }

        return $this->cutCommonData($result);
    }

    private function cutCommonData(array $data): array
    {
        $cutArray = [
            '__initializer__',
            '__cloner__',
            '__isInitialized__',
        ];

        foreach ($data as $key => $value) {
            if (in_array($key, $cutArray) && empty($value)) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
