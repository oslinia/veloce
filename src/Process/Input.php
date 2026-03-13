<?php

declare(strict_types=1);

namespace Veloce\Process;

/**
 * Объект-обертка для безопасного чтения входных данных (POST/GET).
 * 
 * @property-read mixed $... Динамический доступ к полям данных.
 */
readonly class Input
{
    /** @var array<string, string> Ошибки валидации */
    private array $errors;

    /**
     * @param array<string, mixed> $data Массив входящих данных.
     */
    public function __construct(private array $data, array $errors = [])
    {
        // В readonly классе свойства нельзя изменять, поэтому передаем ошибки через конструктор
        $this->errors = $errors;
    }

    /**
     * Магическое получение данных.
     */
    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Валидация данных по правилам.
     * Поддерживает: required, int, email, min:value, max:value
     * 
     * @param array<string, string> $rules ['field' => 'required|min:3|email']
     * @return self Новый экземпляр Input с результатами валидации.
     */
    public function validate(array $rules): self
    {
        $errors = $this->errors;

        foreach ($rules as $field => $ruleString) {
            $rulesArray = explode('|', $ruleString);
            $value = (string)($this->data[$field] ?? '');

            foreach ($rulesArray as $rule) {
                // Разделяем правило и его параметр (например, min:3)
                [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

                switch ($ruleName) {
                    case 'required':
                        if ($value === '') {
                            $errors[$field] = "Поле {$field} обязательно для заполнения.";
                        }
                        break;

                    case 'int':
                        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
                            $errors[$field] = "Поле {$field} должно быть целым числом.";
                        }
                        break;

                    case 'email':
                        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = "Введите корректный email адрес.";
                        }
                        break;

                    case 'min':
                        if ($value !== '' && mb_strlen($value) < (int)$param) {
                            $errors[$field] = "Минимальная длина поля {$field}: {$param} симв.";
                        }
                        break;

                    case 'max':
                        if ($value !== '' && mb_strlen($value) > (int)$param) {
                            $errors[$field] = "Максимальная длина поля {$field}: {$param} симв.";
                        }
                        break;
                }
            }
        }

        return new self($this->data, $errors);
    }

    /**
     * Проверка, прошла ли валидация.
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Получить список ошибок.
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Возвращает первую ошибку для конкретного поля.
     * 
     * @param string $field Имя поля.
     * @return string|null Текст ошибки или null.
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Возвращает данные как строку, очищенную от тегов и пробелов.
     * 
     * @param string $name Ключ данных.
     * @param string $default Значение по умолчанию.
     */
    public function string(string $name, string $default = ''): string
    {
        $value = $this->data[$name] ?? $default;
        return trim(strip_tags((string)$value));
    }

    /**
     * Возвращает данные, приведенные к целому числу.
     * 
     * @param string $name Ключ данных.
     * @param int $default Значение по умолчанию.
     */
    public function int(string $name, int $default = 0): int
    {
        return isset($this->data[$name]) ? (int)$this->data[$name] : $default;
    }

    /**
     * Возвращает массив данных (например, из чекбоксов).
     * 
     * @param string $name Ключ данных.
     * @return array<mixed>
     */
    public function array(string $name): array
    {
        return is_array($this->data[$name] ?? null) ? $this->data[$name] : [];
    }

    /**
     * Возвращает все полученные данные.
     * 
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }
}
