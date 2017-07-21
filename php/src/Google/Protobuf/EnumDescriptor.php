<?php

namespace Google\Protobuf;

interface EnumDescriptor
{
    /**
     * @return string Full protobuf message name
     */
    public function getFullName();

    /**
     * @return string PHP class name
     */
    public function getClass();

    /**
     * @param int $index Must be >= 0 and < getValueCount()
     * @return EnumValueDescriptor
     */
    public function getValue($index);

    /**
     * @return int Number of values in enum
     */
    public function getValueCount();
}
