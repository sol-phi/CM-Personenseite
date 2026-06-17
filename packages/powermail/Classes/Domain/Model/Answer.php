<?php

declare(strict_types=1);

namespace In2code\Powermail\Domain\Model;

use DateTime;
use In2code\Powermail\Exception\DeprecatedException;
use In2code\Powermail\Utility\ArrayUtility;
use In2code\Powermail\Utility\LocalizationUtility;
use Throwable;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Answer extends AbstractEntity
{
    const TABLE_NAME = 'tx_powermail_domain_model_answer';

    const VALUE_TYPE_TEXT = 0;

    const VALUE_TYPE_ARRAY = 1;

    const VALUE_TYPE_DATE = 2;

    const VALUE_TYPE_UPLOAD = 3;

    const VALUE_TYPE_PASSWORD = 4;

    /**
     * This annotation is needed to make the property mapping work. If not set, there will be an exception.
     * See also https://forge.typo3.org/issues/107427
     *
     * @var string $value
     */
    protected string $value = '';

    /**
     * Use when password is hashed so that the originally entered value is available in the finishers
     *
     * @var string $originalValue
     */
    protected string $originalValue = '';

    /**
     * valueType
     *      0 => text
     *      1 => array
     *      2 => date
     *      3 => upload
     *      4 => password
     */
    protected ?int $valueType = null;

    /**
     * @var Mail|null
     */
    protected ?Mail $mail = null;

    /**
     * @var Field|null
     */
    protected ?Field $field = null;

    /**
     * @var string
     */
    protected string $translateFormat = '';

    /**
     * All mails and answers should be stored with sys_language_uid=-1 to get those values from persisted objects
     * in fe requests in every language (e.g. for optin mails, etc...)
     */
    protected ?int $_languageUid = -1;

    /**
     * @throws DeprecatedException
     */
    public function getValue(): string|array|int|null
    {
        $value = $this->value;

        // if serialized, change to array
        // only if type multivalue or upload
        if (
            ArrayUtility::isJsonArray($value)
            && (
                $this->getValueType() === self::VALUE_TYPE_ARRAY
                || $this->getValueType() === self::VALUE_TYPE_UPLOAD
            )
        ) {
            $value = json_decode((string)$value, true);
        }

        if ($this->isTypeDateForTimestamp($value)) {
            $value = date(
                LocalizationUtility::translate('datepicker_format_' . $this->getField()->getDatepickerSettings()),
                (int)$value
            );
        }

        if ($this->isTypeMultiple($value)) {
            return empty($value) ? [] : [(string)$value];
        }

        return $value;
    }

    /**
     * Sets the value
     */
    public function setValue(string|int|array|null $value): Answer
    {
        $value = $this->convertToJson($value);
        $value = $this->convertToTimestamp($value);
        $this->value = (string)$value;
        return $this;
    }

    public function getOriginalValue(): string
    {
        if ($this->originalValue !== '' && $this->originalValue !== $this->value) {
            return $this->originalValue;
        }

        return $this->value;
    }

    public function setOriginalValue(string $originalValue): void
    {
        $this->originalValue = $originalValue;
    }

    /**
     * Returns value and enforces a string
     *        An array will be returned as commaseparated string
     */
    public function getStringValue(string $glue = ', '): string
    {
        $value = $this->getValue();
        if (is_array($value)) {
            $value = implode($glue, $value);
        }

        return (string)$value;
    }

    /**
     * Returns raw value - could be
     *        - Same as getValue()
     *        - Timestamp (Date fields) instead of human readable date
     *        - JSON string for multiple fields instead of array
     */
    public function getRawValue(): string
    {
        return $this->value;
    }

    public function setValueType(int $valueType): Answer
    {
        $this->valueType = $valueType;
        return $this;
    }

    /**
     * @throws DeprecatedException
     */
    public function getValueType(): ?int
    {
        if ($this->valueType === null) {
            if ($this->getField() instanceof Field) {
                $field = $this->getField();
                $this->setValueType($field->dataTypeFromFieldType($field->getType()));
            } else {
                $this->setValue('0');
            }
        }

        return $this->valueType;
    }

    public function getMail(): ?Mail
    {
        return $this->mail;
    }

    public function setMail(Mail $mail): Answer
    {
        $this->mail = $mail;
        return $this;
    }

    public function getField(): ?Field
    {
        return $this->field;
    }

    public function setField(Field $field): Answer
    {
        $this->field = $field;
        return $this;
    }

    protected function isTypeDateForTimestamp(string|array $value): bool
    {
        return $this->getValueType() === self::VALUE_TYPE_DATE && is_numeric($value) && $this->getField() instanceof Field;
    }

    protected function isTypeDateForDate(string|array $value): bool
    {
        if (is_object($this->getField()) || is_string($this->getField())) {
            return !empty($value) && method_exists($this->getField(), 'getType')
            && $this->getValueType() === self::VALUE_TYPE_DATE && !is_numeric($value);
        }

        return false;
    }

    /**
     * If multitext or upload force array
     */
    protected function isTypeMultiple(string|array $value): bool
    {
        return ($this->getValueType() === self::VALUE_TYPE_ARRAY || $this->getValueType() === self::VALUE_TYPE_UPLOAD)
            && !is_array($value);
    }

    /**
     * If it is an array, encode to JSON string
     */
    protected function convertToJson(string|int|array|null $value): string
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return (string)$value;
    }

    /**
     * Convert string to timestamp for date fields (datepicker)
     */
    protected function convertToTimestamp(string $value): int|string
    {
        if ($this->isTypeDateForDate($value)) {
            if (empty($this->translateFormat)) {
                $format = LocalizationUtility::translate(
                    'datepicker_format_' . $this->getField()->getDatepickerSettings()
                );
            } else {
                $format = $this->translateFormat;
            }

            $date = DateTime::createFromFormat($format, $value);
            if ($date) {
                if ($this->getField()->getDatepickerSettings() === 'date') {
                    $date->setTime(0, 0);
                }

                $value = $date->getTimestamp();
            } else {
                try {
                    // fallback html5 date field - always Y-m-d H:i
                    $date = new DateTime($value);
                } catch (Throwable) {
                    // clean value if string could not be converted
                    $value = '';
                }

                if ($date) {
                    if ($this->getField()->getDatepickerSettings() === 'date') {
                        $date->setTime(0, 0);
                    }

                    $value = $date->getTimestamp();
                }
            }
        }

        return $value;
    }
}
