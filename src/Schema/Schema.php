<?php

namespace Racoon\Api\Schema;


use Racoon\Api\Exception\InvalidArgumentException;

class Schema
{

    /**
     * @var Item[]
     */
    protected $items = [];


    /**
     * Schema constructor.
     */
    public function __construct()
    {
    }


    /**
     * @param array $items
     * @return static
     */
    public static function create(array $items = [])
    {
        $schema = new static();
        $schema->addItems($items);
        return $schema;
    }


    /**
     * @param array|Item[]|Translator[] $items
     */
    public function addItems(array $items)
    {
        foreach ($items as $item) {
            if (is_a($item, Translator::class)) {
                $item = $item->returnItem();
            }
            $this->addItem($item);
        }
    }


    /**
     * @param Item $item
     * @return bool
     */
    public function addItem(Item $item)
    {
        $result = false;

        if (! $this->hasItem($item)) {
            $result = true;
            $this->items[] = $item;
        }

        return $result;
    }


    /**
     * @param Item $item
     * @return mixed
     */
    public function hasItem(Item $item)
    {
        return in_array($item, $this->items);
    }


    /**
     * @param Item $item
     * @return bool
     */
    public function removeItem(Item $item)
    {
        $result = false;

        $index = array_search($item, $this->items);
        if ($index !== false) {
            $result = true;
            unset($this->items[$index]);
        }

        return $result;
    }


    /**
     * @param \stdClass $request
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validate($request)
    {
        $passed = true;

        foreach ($this->items as $item) {
            $itemPassed = $item->validate($request);
            if (! $itemPassed) {
                $passed = false;
                break;
            }
        }

        if (! $passed) {
            $requirements = $item->getRequirements();
            throw new InvalidArgumentException(null, $requirements);
        }
        
        return $passed;
    }


    /**
     * @return Item[]
     */
    public function getItems()
    {
       return $this->items;
    }


    /**
     * @return \stdClass
     */
    public function getDefinition()
    {
        $result = new \stdClass();

        foreach ($this->items as $item) {
            $result->{$item->getPropertyName()} = $item->getRequirements();
        }

        return $result;
    }

}