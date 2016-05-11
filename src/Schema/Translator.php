<?php

namespace Racoon\Api\Schema;


use TomWright\Validator\Constraint\BoolConstraint;
use TomWright\Validator\Constraint\ConstraintGroup;
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
     * @var ConstraintGroup
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
     * @return ConstraintGroup
     */
    public function getCurrentConstraintGroup()
    {
        if (! $this->hasCurrentConstraintGroup()) {
            $this->currentConstraintGroup = ConstraintGroup::create();
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
     * @param ConstraintGroup|null $group
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
        $constraint = BoolConstraint::create();
        if ($requiredValue !== null) {
            $constraint->setRequiredValue($requiredValue);
        }

        $group = $this->getCurrentConstraintGroup();
        $group->addConstraint($constraint);

        return $this;
    }


    /**
     * @return Translator
     */
    public function isTrue()
    {
        return $this->isBool(true);
    }


    /**
     * @return Translator
     */
    public function isFalse()
    {
        return $this->isBool(false);
    }


    /**
     * @param null $minLength
     * @param null $maxLength
     * @return $this
     */
    public function isString($minLength = null, $maxLength = null)
    {
        $constraint = StringConstraint::create();
        if ($minLength !== null) {
            $constraint->setMinLength($minLength);
        }
        if ($maxLength !== null) {
            $constraint->setMinLength($maxLength);
        }

        $group = $this->getCurrentConstraintGroup();
        $group->addConstraint($constraint);

        return $this;
    }


    /**
     * @param null $minValue
     * @param null $maxValue
     * @return $this
     */
    public function isInt($minValue = null, $maxValue = null)
    {
        $constraint = IntConstraint::create();
        if ($minValue !== null) {
            $constraint->setMinValue($minValue);
        }
        if ($maxValue !== null) {
            $constraint->setMaxValue($maxValue);
        }

        $group = $this->getCurrentConstraintGroup();
        $group->addConstraint($constraint);

        return $this;
    }


    /**
     * @return $this
     */
    public function isNull()
    {
        $constraint = NullConstraint::create();

        $group = $this->getCurrentConstraintGroup();
        $group->addConstraint($constraint);

        return $this;
    }


    /**
     * @return $this
     */
    public function notNull()
    {
        $constraint = NotNullConstraint::create();

        $group = $this->getCurrentConstraintGroup();
        $group->addConstraint($constraint);

        return $this;
    }


    /**
     * @return $this
     */
    public function isEmail()
    {
        $constraint = EmailConstraint::create();

        $group = $this->getCurrentConstraintGroup();
        $group->addConstraint($constraint);

        return $this;
    }


    /**
     * @return $this
     */
    public function isArray()
    {
        $constraint = ArrayConstraint::create();

        $group = $this->getCurrentConstraintGroup();
        $group->addConstraint($constraint);

        return $this;
    }


    /**
     * @param null|string $requiredClass
     * @return $this
     */
    public function isObject($requiredClass = null)
    {
        $constraint = ObjectConstraint::create();
        if ($requiredClass !== null) {
            $constraint->setRequiredClass($requiredClass);
        }

        $group = $this->getCurrentConstraintGroup();
        $group->addConstraint($constraint);

        return $this;
    }

}