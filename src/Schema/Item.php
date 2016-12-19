<?php

namespace Racoon\Api\Schema;


use Racoon\Api\Exception\InvalidArgumentException;
use TomWright\Validator\Constraint\ConstraintGroup;
use TomWright\Validator\Constraint\NullConstraint;
use TomWright\Validator\Validator;

class Item
{

    /**
     * @var null|string
     */
    protected $propertyName;

    /**
     * @var null|mixed
     */
    protected $propertyValue;

    /**
     * @var null|string
     */
    protected $readableName;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var ConstraintGroup
     */
    protected $optionalConstraintGroup;


    /**
     * Item constructor.
     * @param string $propertyName
     * @param string $readableName
     * @param array|ConstraintGroup[] $constraintGroups
     * @param bool $required
     * @param ConstraintGroup $optionalConstraintGroup
     */
    public function __construct($propertyName, $readableName, array $constraintGroups = [], $required = true, ConstraintGroup $optionalConstraintGroup = null)
    {
        $this->validator = new Validator(null, null);
        $this->setPropertyName($propertyName);
        $this->setReadableName($readableName);
        $this->setRequired($required);
        if (is_array($constraintGroups) && count($constraintGroups) > 0) {
            foreach ($constraintGroups as $constraintGroup) {
                $this->validator->addConstraintGroup($constraintGroup);
            }
        }
        $this->setOptionalConstraintGroup($optionalConstraintGroup);
    }


    /**
     * @param $propertyName
     * @param $readableName
     * @param array|ConstraintGroup[] $constraintGroups
     * @param bool $required
     * @param ConstraintGroup $optionalConstraintGroup
     * @return static
     */
    public static function create($propertyName, $readableName, array $constraintGroups = [], $required = true, ConstraintGroup $optionalConstraintGroup = null)
    {
        $item = new static($propertyName, $readableName, $constraintGroups, $required, $optionalConstraintGroup);
        return $item;
    }


    /**
     * @param \stdClass $request
     * @return bool
     * @throws InvalidArgumentException
     * @throws \TomWright\Validator\Exception\FailedConstraintException
     */
    public function validate($request)
    {
        $valueExists = (is_object($request) && property_exists($request, $this->getPropertyName()));
        if ($this->isRequired() && ! $valueExists) {
            throw new InvalidArgumentException(null, "Missing required field: {$this->getPropertyName()}");
        } elseif (! $this->isRequired()) {
            if (! $valueExists) {
                $this->validator->addConstraintGroup(
                    ConstraintGroup::create([
                        NullConstraint::create(),
                    ])
                );
            } elseif (is_object($this->getOptionalConstraintGroup())) {
                $this->validator->addConstraintGroup($this->getOptionalConstraintGroup());
            }
        }

        $value = null;
        if ($valueExists) {
            $value = $request->{$this->getPropertyName()};
        }
        $this->setPropertyValue($value);

        return $this->validator->validate()->hasPassed();
    }


    /**
     * @return null|string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }


    /**
     * @param null|string $propertyName
     * @return $this
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
        return $this;
    }


    /**
     * @return mixed|null
     */
    public function getPropertyValue()
    {
        return $this->propertyValue;
    }


    /**
     * @param mixed|null $propertyValue
     * @return $this
     */
    public function setPropertyValue($propertyValue)
    {
        $this->propertyValue = $propertyValue;
        $this->validator->setValue($propertyValue);
        return $this;
    }


    /**
     * @return null|string
     */
    public function getReadableName()
    {
        return $this->readableName;
    }


    /**
     * @param null|string $readableName
     * @return $this
     */
    public function setReadableName($readableName)
    {
        $this->readableName = $readableName;
        $this->validator->setName($readableName);
        return $this;
    }


    /**
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }


    /**
     * @return string
     */
    public function getRequirements()
    {
        return $this->getValidator()->getRequirementsString();
    }


    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }


    /**
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }


    /**
     * @return ConstraintGroup
     */
    public function getOptionalConstraintGroup()
    {
        return $this->optionalConstraintGroup;
    }


    /**
     * @param ConstraintGroup $optionalConstraintGroup
     */
    public function setOptionalConstraintGroup($optionalConstraintGroup)
    {
        $this->optionalConstraintGroup = $optionalConstraintGroup;
    }

}