<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits\RequestHandler\Traits;

use Genocide\Radiocrud\Exceptions\CustomException;
use Genocide\Radiocrud\Helpers;
use Illuminate\Http\UploadedFile;
use Morilog\Jalali\CalendarUtils;

trait CastsHandler
{
    protected array $casts = [];

    /**
     * @param array $casts
     * @return $this
     */
    public function setCasts (array $casts): static
    {
        $this->casts = $casts;
        return $this;
    }

    /**
     * @return array
     */
    public function getCasts (): array
    {
        return $this->casts;
    }

    /**
     * @param array $data
     * @param array $casts
     * @param string $prefix
     * @return array
     * @throws CustomException
     */
    protected function castData(array $data, array $casts = [], string $prefix = ''): array
    {
        return $this->castDataHelper($data, array_merge($this->casts, $casts), $prefix);
    }

    /**
     * @param array $data
     * @param array $casts
     * @param string $prefix
     * @return array
     * @throws CustomException
     */
    protected function castDataHelper(array $data, array $casts, string $prefix): array
    {
        foreach ($data as $field => $value)
        {
            if (is_array($value))
            {
                $data[$field] = $this->castDataHelper($value, $casts, "$prefix$field.");
                continue;
            }
            if (isset($casts["$prefix*"]))
            {
                $data[$field] = $this->cast("$prefix*", $value, $casts["$prefix*"]);
            }
            if (isset($casts["$prefix$field"]))
            {
                $data[$field] = $this->cast("$prefix$field", $value, $casts["$prefix$field"]);
            }
        }
        return $data;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array|string $castRules
     * @return mixed
     * @throws CustomException
     */
    protected function cast(string $field, mixed $value, array|string $castRules): mixed
    {
        if (is_string($castRules))
        {
            $castRules = explode('|', $castRules);
        }

        if (empty($value))
        {
            if (!in_array('nullable', $castRules))
            {
                throw new CustomException("$field should not be empty");
            }
            return $value;
        }

        foreach ($castRules as $castRule)
        {
            if ($castRule == 'nullable')
            {
                continue;
            }

            $castRule = explode(':', $castRule, 2);
            $value = match ($castRule[0]) {
                'file' => $this->uploadFile($value, $castRule[1] ?? '', $field),
                'boolean' => Helpers::convertToBoolean($value),
                'regex' => $this->checkRegex($value, $castRule[1], $field),
                'jalali_to_gregorian' => $this->castJalaliDate($value, $castRule[1] ?? 'Y-m-d H:i:s', $field),
                default => $value,
            };
        }

        return $value;
    }

    /**
     * @param string $date
     * @param string $dateFormat
     * @param string|null $field
     * @return array|string
     */
    protected function castJalaliDate (string $date, string $dateFormat = 'Y-m-d H:i:s', string $field = null): array|string
    {
        /*
        $arrayDateTime = explode(' ', $date);
        $arrayDate = explode('-', $arrayDateTime[0]);
        if (count($arrayDate) != 3)
        {
            throw new CustomException("wrong date at $field field", 800, 400);
        }
        $date = jalali_to_gregorian($arrayDate[0], $arrayDate[1], $arrayDate[2], '-');
        if (isset($arrayDateTime[1]))
        {
            $date .= " $arrayDateTime[1]";
        }
        return $date;
        */
        $dateFormat = explode('->', $dateFormat, 2);
        return CalendarUtils::createDatetimeFromFormat($dateFormat[0], $date)->format($dateFormat[1] ?? $dateFormat[0]);
    }

    /**
     * @param string $string
     * @param string $regex
     * @param string|null $field
     * @return string
     * @throws CustomException
     */
    protected function checkRegex(string $string, string $regex, string $field = null): string
    {
        preg_match($regex, $string, $matches);

        if (empty($matches))
        {
            throw new CustomException("could not match $field with required regex pattern", 30, 400);
        }

        return $matches[0];
    }

    /**
     * @param UploadedFile $file
     * @param string $path
     * @param string|null $fieldName
     * @return string
     */
    protected function uploadFile(UploadedFile $file, string $path = '/uploads', string $fieldName = null): string
    {
        if (empty($path))
        {
            $path = '/uploads';
        }
        return $file->store($path);
    }
}