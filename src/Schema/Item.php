<?php

namespace Racoon\Api\Schema;


use TomWright\Validator\Constraint\ConstraintGroup;
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
     * Item constructor.
     * @param string $propertyName
     * @param string $readableName
     * @param array|ConstraintGroup[] $constraintGroups
     */
    public function __construct($propertyName, $readableName, array $constraintGroups = [])
    {
        $this->validator = new Validator(null, null);
        $this->setPropertyName($propertyName);
        $this->setReadableName($readableName);
        if (is_array($constraintGroups) && count($constraintGroups) > 0) {
            foreach ($constraintGroups as $constraintGroup) {
                $this->validator->addConstraintGroup($constraintGroup);
            }
        }
    }


    /**
     * @param $propertyName
     * @param $readableName
     * @param array|ConstraintGroup[] $constraintGroups
     * @return static
     */
    public static function create($propertyName, $readableName, array $constraintGroups = [])
    {
        $item = new static($propertyName, $readableName, $constraintGroups);
        return $item;
    }


    /**
     * @param \stdClass $request
     * @return bool
     */
    public function validate($request)
    {
        $value = null;
        if (is_object($request) && isset($request->{$this->getPropertyName()})) {
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

}