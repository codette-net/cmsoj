<?php

namespace CMSOJ\Helpers;

class Validator
{
    protected array $data;
    protected array $rules;
    protected array $errors = [];

    public static function make(array $data, array $rules): self
    {
        $v = new self;
        $v->data  = $data;
        $v->rules = $rules;
        $v->validate();
        return $v;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = trim($this->data[$field] ?? '');

            foreach ($rules as $rule) {
                if ($rule === 'required' && $value === '') {
                    $this->errors[$field] = ucfirst($field) . " is required.";
                }

                if ($rule === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "Invalid email format.";
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) explode(':', $rule)[1];
                    if ($value !== '' && strlen($value) < $min) {
                        $this->errors[$field] = ucfirst($field) . " must be at least $min characters.";
                    }
                }

                if ($rule === 'nullable' && $value === '') {
                    continue;
                }
            }
        }
    }
}
