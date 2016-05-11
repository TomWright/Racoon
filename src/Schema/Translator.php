<?php

namespace Racoon\Api\Schema;


use TomWright\Validator\Constraint\BoolConstraint;
use TomWright\Validator\Constraint\ConstraintGroup;
use TomWright\Validator\Constraint\ConstraintGroupTranslator;
use TomWright\Validator\Constraint\EmailConstraint;
use TomWright\Validator\Constraint\IntConstraint;
use TomWright\Validator\Constraint\NotNullConstraint;
use TomWright\Validator\Constraint\NullConstraint;
use TomWright\Validator\Constraint\StringConstraint;
use TomWright\Validator\Constraint\ArrayConstraint;
use TomWright\Validator\Constraint\ObjectConstraint;

class Translator
{

    /**
     * @var Item
     */
    protected $item;

    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var string
     */
    protected $readableName;

    /**
     * @var ConstraintGroupTranslator
     */
    protected $currentConstraintGroup;


    /**
     * @return Item
     */
    public function getItem()
    {
        if (! is_object($this->item)) {
            $this->item = new Item($this->propertyName, $this->readableName);
        }
        return $this->item;
    }


    /**
     * @return Item
     */
    public function returnItem()
    {
        if ($this->hasCurrentConstraintGroup()) {
            $this->getItem()->getValidator()->addConstraintGroup($this->getCurrentConstraintGroup());
        }
        return $this->getItem();
    }


    /**
     * An OR, but OR is a keyword.
     * @return $this
     */
    public function newConstraintGroup()
    {
        if ($this->hasCurrentConstraintGroup()) {
            $this->getItem()->getValidator()->addConstraintGroup($this->getCurrentConstraintGroup());
            $this->currentConstraintGroup = null;
        }
        return $this;
    }


    /**
     * @return ConstraintGroupTranslator
     */
    public function getCurrentConstraintGroup()
    {
        if (! $this->hasCurrentConstraintGroup()) {
            $this->currentConstraintGroup = ConstraintGroupTranslator::create();
        }
        return $this->currentConstraintGroup;
    }


    /**
     * @return mixed
     */
    public function hasCurrentConstraintGroup()
    {
        return (is_object($this->currentConstraintGroup));
    }


    /**
     * Alias of newConstraintGroup.
     * @return Translator
     */
    public function alt()
    {
        return $this->newConstraintGroup();
    }


    /**
     * @param string $propertyName
     * @param string|null $readableName
     * @return static
     */
    public static function item($propertyName, $readableName = null)
    {
        $translator = new static();

        $translator->propertyName = $propertyName;
        if ($readableName === null) {
            $readableName = $propertyName;
        }
        $translator->readableName = $readableName;

        return $translator;
    }


    /**
     * @return $this
     */
    public function required()
    {
        $this->getItem()->setRequired(true);
        return $this;
    }


    /**
     * @return $this
     */
    public function optional()
    {
        $this->getItem()->setRequired(false);
        return $this;
    }


    /**
     * @param ConstraintGroup|ConstraintGroupTranslator|null $group
     * @return $this
     */
    public function optionalConstraintGroup(ConstraintGroup $group = null)
    {
        $this->getItem()->setOptionalConstraintGroup($group);
        return $this;
    }


    /**
     * @param null|bool $requiredValue
     * @return $this
     */
    public function isBool($requiredValue = null)
    {
        $this->getCurrentConstraintGroup()->isBool($requiredValue);
        return $this;
    }


    /**
     * @return Translator
     */
    public function isTrue()
    {
        $this->getCurrentConstraintGroup()->isTrue();
        return $this;
    }


    /**
     * @return Translator
     */
    public function isFalse()
    {
        $this->getCurrentConstraintGroup()->isFalse();
        return $this;
    }


    /**
     * @param null $minLength
     * @param null $maxLength
     * @return $this
     */
    public function isString($minLength = null, $maxLength = null)
    {
        $this->getCurrentConstraintGroup()->isString($minLength, $maxLength);
        return $this;
    }


    /**
     * @param null|int $minValue
     * @param null|int $maxValue
     * @return $this
     */
    public function isInt($minValue = null, $maxValue = null)
    {
        $this->getCurrentConstraintGroup()->isInt($minValue, $maxValue);
        return $this;
    }


    /**
     * @param null|float $minValue
     * @param null|float $maxValue
     * @return $this
     */
    public function isFloat($minValue = null, $maxValue = null)
    {
        $this->getCurrentConstraintGroup()->isFloat($minValue, $maxValue);
        return $this;
    }


    /**
     * @param null|int|float $minValue
     * @param null|int|float $maxValue
     * @return $this
     */
    public function isNumeric($minValue = null, $maxValue = null)
    {
        $this->getCurrentConstraintGroup()->isNumeric($minValue, $maxValue);
        return $this;
    }


    /**
     * @return $this
     */
    public function isNull()
    {
        $this->getCurrentConstraintGroup()->isNull();
        return $this;
    }


    /**
     * @return $this
     */
    public function notNull()
    {
        $this->getCurrentConstraintGroup()->notNull();
        return $this;
    }


    /**
     * @return $this
     */
    public function isEmail()
    {
        $this->getCurrentConstraintGroup()->isEmail();
        return $this;
    }


    /**
     * @return $this
     */
    public function isArray()
    {
        $this->getCurrentConstraintGroup()->isArray();
        return $this;
    }


    /**
     * @param null|string $requiredClass
     * @return $this
     */
    public function isObject($requiredClass = null)
    {
        $this->getCurrentConstraintGroup()->isObject($requiredClass);
        return $this;
    }

}