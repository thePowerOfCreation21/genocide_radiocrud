<?php

namespace Genocide\Radiocrud\Services;

use Genocide\Radiocrud\Exceptions\CustomException;
use Genocide\Radiocrud\Models\KeyValueConfig;
use Genocide\Radiocrud\Services\ActionService\Traits\RequestHandler\RequestHandler;
use Illuminate\Http\Request;

class KeyValueConfigService
{
    use RequestHandler;

    protected string $key = 'default_key';

    protected array $default_values = [];

    protected array|null $general_value = null;

    protected array $validation_rule = [];

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return (new static)->$name(...$arguments);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return array|mixed|null
     */
    public function __call(string $name, array $arguments)
    {
        return match ($name)
        {
            'get' => $this->getHelper(...$arguments),
            'update' => $this->updateHelper(...$arguments),
        };
    }

    /**
     * @param bool $force_get_from_db
     * @param bool $force_apply_default_values
     * @return array|null
     */
    public function getHelper(bool $force_get_from_db = false, bool $force_apply_default_values = false): ?array
    {
        if (is_null($this->general_value) || $force_get_from_db)
        {
            $keyValueConfig = KeyValueConfig::where('key', $this->key)->first();
            if (!empty($keyValueConfig))
            {
                if (is_object($keyValueConfig->value))
                {
                    $general_value = (array) $keyValueConfig->value;
                }
            }
            $this->general_value = $general_value ?? [];
            $force_apply_default_values = true;
        }
        if (!is_array($this->general_value))
        {
            $this->general_value = [];
        }
        if (empty($this->general_value) || $force_apply_default_values)
        {
            $this->apply_default_values_to_general_value();
        }
        return $this->general_value;
    }

    /**
     * @return void
     */
    public function apply_default_values_to_general_value ()
    {
        $this->general_value = $this->merge_general_value_with_default_values($this->general_value, $this->default_values);
    }

    /**
     * @param array $general_value
     * @param array|null $default_values
     * @return array
     */
    public function merge_general_value_with_default_values (array $general_value, array $default_values = null): array
    {
        if (is_null($default_values))
        {
            $default_values = $this->default_values;
        }
        foreach ($default_values AS $default_field => $default_value)
        {
            if (is_array($default_value))
            {
                if (isset($general_value[$default_field]) && is_array($general_value[$default_field]))
                {
                    $general_value[$default_field] = $this->merge_general_value_with_default_values($general_value[$default_field], $default_value);
                }
                else
                {
                    $general_value[$default_field] = $default_value;
                }
                continue;
            }
            $general_value[$default_field] = $general_value[$default_field] ?? $default_value;
        }
        return $general_value;
    }

    /**
     * @return void
     * @throws CustomException
     */
    public function update_by_request ()
    {
        $this->update(
            $this->getDataFromRequest()
        );
    }

    /**
     * @param array $new_general_value
     * @return bool
     */
    public function updateHelper (array $new_general_value): bool
    {
        $new_general_value = $this->merge_general_value_with_default_values($new_general_value, $this->get());
        if ($new_general_value != $this->general_value)
        {
            $this->general_value = $new_general_value;
            $this->save_general_value();
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function save_general_value (): mixed
    {
        KeyValueConfig::where('key', $this->key)->delete();
        return KeyValueConfig::create([
            'key' => $this->key,
            'value' => $this->general_value
        ]);
    }
}
