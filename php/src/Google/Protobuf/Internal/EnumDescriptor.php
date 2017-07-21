<?php

namespace Google\Protobuf\Internal;

class EnumDescriptor
{
    private $full_name;
    private $klass;
    private $values;

    public function __construct($full_name, $klass, $values)
    {
        $this->full_name = $full_name;
        $this->klass = $klass;
        $this->values = $values;
    }

    public function getFullName()
    {
        return $this->full_name;
    }

    public function getClass()
    {
        return $this->klass;
    }

    public function getValue($number)
    {
        return $this->values[$number];
    }

    public function getValueCount()
    {
        return count($this->values);
    }

    public static function buildFromProto($proto, $file_proto, $containing)
    {
        $enum_name_without_package  = "";
        $classname = "";
        $fullname = "";
        GPBUtil::getFullClassName(
            $proto,
            $containing,
            $file_proto,
            $enum_name_without_package,
            $classname,
            $fullname);
        $value_arr = [];
        foreach ($proto->getValue() as $proto_value) {
            $name = $proto_value->getName();
            $number = $proto_value->getNumber();
            $value_arr[$number] = new EnumValueDescriptor($name, $number);
        }

        return new EnumDescriptor($fullname, $classname, $value_arr);
    }
}
