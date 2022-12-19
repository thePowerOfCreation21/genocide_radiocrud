<?php

namespace Genocide\Radiocrud\Services\ActionService\Traits\RequestHandler\Traits;

use Genocide\Radiocrud\Exceptions\CustomException;

trait ValidationRuleHandler
{
    protected array $validationRules = [];

    protected array|string $validationRule = [];

    /**
     * @param array $validationRules
     * @return $this
     */
    public function setValidationRules(array $validationRules): static
    {
        $this->validationRules = $this->castMultipleValidationRulesFromLaravel($validationRules);
        return $this;
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * @param array|string $validationRule
     * @return $this
     * @throws CustomException
     */
    public function setValidationRule(array|string $validationRule): static
    {
        $this->validationRule = is_array($validationRule) ? $this->castValidationRuleFromLaravel($validationRule) : $this->getValidationRuleByName($validationRule);
        return $this;
    }

    /**
     * @param string $validationRuleName
     * @param bool $throwException
     * @return mixed
     * @throws CustomException
     */
    public function getValidationRuleByName (string $validationRuleName, bool $throwException = true): mixed
    {
        if (! isset($this->validationRules[$validationRuleName]))
        {
            if ($throwException) {
                throw new CustomException(
                    "validation role '$validationRuleName' is not set for " . get_class($this),
                    65, 500
                );
            }
            return [
                'laravel' => [],
                'casts' => []
            ];
        }

        return $this->validationRules[$validationRuleName];
    }

    /**
     * @param string|null $validationRuleName
     * @param bool $throwException
     * @return array
     * @throws CustomException
     */
    protected function getValidationRule(string $validationRuleName = null, bool $throwException = true): array
    {
        return $this->castValidationRuleFromLaravel(
            is_null($validationRuleName) ? $this->validationRule : $this->getValidationRuleByName($validationRuleName, $throwException)
        );
    }

    /**
     * @param array|string $validationRule
     * @return array|string
     */
    public function castValidationRuleFromLaravel (array|string $validationRule): array|string
    {
        return isset($validationRule['laravel']) ? $validationRule : ['laravel' => $validationRule];
    }

    /**
     * @param array $validationRules
     * @return array|string
     */
    public function castMultipleValidationRulesFromLaravel (array $validationRules): array|string
    {
        foreach ($validationRules AS $key => $validationRule)
        {
            $validationRules[$key] = $this->castValidationRuleFromLaravel($validationRule);
        }
        return $validationRules;
    }
}