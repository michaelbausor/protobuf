<?php

// Protocol Buffers - Google's data interchange format
// Copyright 2008 Google Inc.  All rights reserved.
// https://developers.google.com/protocol-buffers/
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are
// met:
//
//     * Redistributions of source code must retain the above copyright
// notice, this list of conditions and the following disclaimer.
//     * Redistributions in binary form must reproduce the above
// copyright notice, this list of conditions and the following disclaimer
// in the documentation and/or other materials provided with the
// distribution.
//     * Neither the name of Google Inc. nor the names of its
// contributors may be used to endorse or promote products derived from
// this software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
// "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
// LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
// A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
// OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
// SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
// LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
// DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
// THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
// (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
// OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

namespace Google\Protobuf\Internal;

class FieldDescriptor implements \Google\Protobuf\FieldDescriptor
{
    private $name;
    private $number;
    private $label;
    private $type;
    private $message_type;
    private $enum_type;
    private $oneof_index;
    private $setter;
    private $getter;
    private $json_name;
    private $packed;

    public function __construct(
        $name,
        $number,
        $label,
        $type,
        $message_type,
        $enum_type,
        $oneof_index,
        $setter,
        $getter,
        $json_name,
        $packed)
    {
        $this->name = $name;
        $this->number = $number;
        $this->label = $label;
        $this->type = $type;
        $this->message_type = $message_type;
        $this->enum_type = $enum_type;
        $this->oneof_index = $oneof_index;
        $this->setter = $setter;
        $this->getter = $getter;
        $this->json_name = $json_name;
        $this->packed = $packed;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMessageType()
    {
        return $this->message_type;
    }

    public function getEnumType()
    {
        return $this->enum_type;
    }

    public function isMap()
    {
        return $this->getType() == GPBType::MESSAGE &&
               !is_null($this->getMessageType()->getOptions()) &&
               $this->getMessageType()->getOptions()->getMapEntry();
    }

    public function isRepeated()
    {
        return $this->label === GPBLabel::REPEATED;
    }

    public function getOneofIndex()
    {
        return $this->oneof_index;
    }

    public function getSetter()
    {
        return $this->setter;
    }

    public function getGetter()
    {
        return $this->getter;
    }

    public function getJsonName()
    {
        return $this->json_name;
    }

    public function getPacked()
    {
        return $this->packed;
    }

    public function isPackable()
    {
        return $this->isRepeated() && self::isTypePackable($this->type);
    }

    private static function isTypePackable($field_type)
    {
        return ($field_type !== GPBType::STRING  &&
            $field_type !== GPBType::GROUP   &&
            $field_type !== GPBType::MESSAGE &&
            $field_type !== GPBType::BYTES);
    }

    public function crossLink($pool)
    {
        switch ($this->type) {
            case GPBType::MESSAGE:
                $this->message_type = $pool->getDescriptorByProtoName($this->message_type);
                break;
            case GPBType::ENUM:
                $this->enum_type = $pool->getEnumDescriptorByProtoName($this->enum_type);
                break;
            default:
                break;
        }
    }

    public static function buildFromProto($proto)
    {
        $type = $proto->getType();
        $message_type = null;
        $enum_type = null;
        // At this time, the message/enum type may have not been added to pool.
        // So we use the type name as place holder and will replace it with the
        // actual descriptor in cross building.
        switch ($type) {
            case GPBType::MESSAGE:
                $message_type = $proto->getTypeName();
            case GPBType::ENUM:
                $enum_type = $proto->getTypeName();
                break;
            default:
                break;
        }

        $oneof_index = $proto->hasOneofIndex() ? $proto->getOneofIndex() : -1;

        if ($proto->hasJsonName()) {
            $json_name = $proto->getJsonName();
        } else {
            $proto_name = $proto->getName();
            $json_name = implode('', array_map('ucwords', explode('_', $proto_name)));
            if ($proto_name[0] !== "_" && !ctype_upper($proto_name[0])) {
                $json_name = lcfirst($json_name);
            }
        }

        $camel_name = implode('', array_map('ucwords', explode('_', $proto->getName())));
        $setter = 'set' . $camel_name;
        $getter = 'get' . $camel_name;

        $packed = false;
        $options = $proto->getOptions();
        if ($options !== null) {
            $packed = $options->getPacked();
        }

        return new FieldDescriptor(
            $proto->getName(),
            $proto->getNumber(),
            $proto->getLabel(),
            $type,
            $message_type,
            $enum_type,
            $oneof_index,
            $setter,
            $getter,
            $json_name,
            $packed
        );
    }
}
