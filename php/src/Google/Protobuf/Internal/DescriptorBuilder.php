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

class DescriptorBuilder
{
    private $full_name;
    private $klass;
    private $fields = [];
    private $nested_types = [];
    private $enum_types = [];
    private $oneof_decls = [];

    public function __construct($full_name, $klass)
    {
        $this->full_name = $full_name;
        $this->klass = $klass;
    }

    public function getFullName()
    {
        return $this->full_name;
    }

    public function getClass()
    {
        return $this->klass;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getNestedTypes()
    {
        return $this->nested_types;
    }

    public function getEnumTypes()
    {
        return $this->enum_types;
    }

    private function getFieldDescriptor($name, $label, $type,
                                      $number, $type_name = null)
    {
        $message_type = null;
        $enum_type = null;

        // At this time, the message/enum type may have not been added to pool.
        // So we use the type name as place holder and will replace it with the
        // actual descriptor in cross building.
        switch ($type) {
        case GPBType::MESSAGE:
            $message_type = $type_name;
            break;
        case GPBType::ENUM:
            $enum_type = $type_name;
            break;
        default:
            break;
        }

        $camel_name = implode('', array_map('ucwords', explode('_', $name)));
        $setter = 'set' . $camel_name;
        $getter = 'get' . $camel_name;

        return new FieldDescriptor(
            $name,
            $number,
            $label,
            $type,
            $message_type,
            $enum_type,
            -1,
            $setter,
            $getter,
            null,
            false
        );
    }

    public function optional($name, $type, $number, $type_name = null)
    {
        return $this->addField($name, GPBLabel::OPTIONAL, $type, $number, $type_name);
    }

    public function repeated($name, $type, $number, $type_name = null)
    {
        return $this->addField($name, GPBLabel::REPEATED, $type, $number, $type_name);
    }

    public function required($name, $type, $number, $type_name = null)
    {
        return $this->addField($name, GPBLabel::REQUIRED, $type, $number, $type_name);
    }

    private function addField($name, $label, $type, $number, $type_name = null)
    {
        $this->fields[] = $this->getFieldDescriptor(
            $name,
            $label,
            $type,
            $number,
            $type_name);
        return $this;
    }

    public function build()
    {
        return new Descriptor(
            $this->full_name,
            $this->klass,
            $this->fields,
            $this->nested_types,
            $this->enum_types,
            $this->oneof_decls,
            null
        );
    }
}
