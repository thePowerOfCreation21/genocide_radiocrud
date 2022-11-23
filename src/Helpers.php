<?php

namespace Genocide\Radiocrud;

use Exception;
use Morilog\Jalali\CalendarUtils;

class Helpers
{
    /**
     * @param string $fileName
     * @return string|bool
     */
    public static function getFileExtension(string $fileName): string|bool
    {
        $array  = explode(".", $fileName);
        return end($array);
    }

    /**
     * @param mixed $mixed
     * @return string|null
     */
    public static function sanitize(mixed $mixed): ?string
    {
        $result = null;
        $mixed_type = gettype($mixed);
        $valid_types = ["string", "integer", "double"];
        if (in_array($mixed_type, $valid_types))
        {
            $result = htmlentities($mixed, ENT_QUOTES, 'UTF-8');
        }
        return $result;
    }

    /**
     * @param null $input
     * @param null $default
     * @param array $notBe
     * @return mixed
     */
    public static function sis(mixed &$input = null, mixed $default = null, array $notBe=[]): mixed
    {
        $ret = $default;
        if (isset($input))
        {
            if(!in_array($input,$notBe))
            {
                $ret = $input;
            }
        }
        else
        {
            unset($input);
        }
        return $ret;
    }

    /**
     * @param $file
     * @param array $allowedFormats
     * @param string $direction
     * @param bool $changeFileName
     * @param bool $deleteIfDuplicate
     * @return string|null
     */
    public static function UploadIt ($file, array $allowedFormats=['png','jpg','jpeg'], string $direction="uploads/", bool $changeFileName = true, bool $deleteIfDuplicate = true): ?string
    {
        $fileDirection=null;
        $fileExt = self::getFileExtension(self::sis($file['name']));
        $fileExt = strtolower($fileExt);
        if(in_array($fileExt , $allowedFormats)){
            if ($changeFileName)
            {
                $fileName=time()."_".rand(1,1000000)."_".".$fileExt";
            }
            else
            {
                $fileName = self::sanitize($file['name']);
            }

            $fileDirection=$direction.$fileName;

            if ($deleteIfDuplicate && is_file($fileDirection))
            {
                unlink($fileDirection);
            }

            move_uploaded_file($file['tmp_name'],$fileDirection);
        }
        return $fileDirection;
    }

    /**
     * @param string $time
     * @return object
     */
    public static function timeToCustomDate (string $time = 'current_time'): object
    {
        if (! is_numeric($time))
        {
            $time = time();
        }

        $time = (int) $time;

        return (object) [
            'timestamp' => $time,
            'date' => date("Y-m-d H:i:s", $time),
            'jdate' => CalendarUtils::strftime("Y-m-d H:i:s", $time),
            'string' => CalendarUtils::strftime("l j F Y", $time),
        ];
    }

    /**
     * @param mixed $mixed
     * @return bool
     */
    public static function convertToBoolean (mixed $mixed): bool
    {
        if (is_string($mixed))
        {
            $falsyValues = [0, 'false'];
            return !in_array($mixed, $falsyValues);
        }
        else
        {
            return !empty($mixed);
        }
    }

    /**
     * @param mixed $mixed
     * @return array
     * @throws Exception
     */
    public static function convertToArray (mixed $mixed): array
    {
        if (is_object($mixed))
        {
            $mixed = (array) $mixed;
        }

        if (!is_array($mixed))
        {
            throw new Exception('value should be array or object');
        }

        return $mixed;
    }

    /**
     * checks if given array is associative or not
     *
     * @param array $arr
     * @return bool
     */
    public static function isAssoc(array $arr): bool
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @return bool|int
     */
    public static function getDailyTime (): bool|int
    {
        $time = (time() - strtotime('today'));
        if (date('H', strtotime('today')) == '01')
        {
            $time += 3600;
        }
        return $time;
    }

    /**
     * @param string $str
     * @return bool|int
     */
    public static function strToDailyTime (string $str): bool|int
    {
        $time = strtotime(date('Y/m/j', strtotime($str)));
        if (date('H', $time) == '01')
        {
            $time -= 3600;
        }
        return $time;
    }

    /**
     * @param $value
     * @return string
     */
    public static function setCustomDateCast ($value): string
    {
        if (!is_numeric($value))
        {
            if (empty($value) || !is_string($value))
            {
                $value = time();
            }
            else
            {
                $value = strtotime($value);
            }
        }

        return date("Y-m-d H:i:s", $value);
    }

    /**
     * @param $value
     * @return object|null
     */
    public static function getCustomDateCast ($value): ?object
    {
        return empty($value) ? null : Helpers::timeToCustomDate(
            strtotime($value)
        );
    }
}
