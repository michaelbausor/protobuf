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

class Descriptor implements \Google\Protobuf\Descriptor
{
    private $full_name;
    private $klass;
    private $fields;
    private $number_to_fields;
    private $json_to_fields;
    private $name_to_fields;
    private $nested_types;
    private $enum_types;
    private $oneof_decls;
    private $options;

    public function __construct(
        $full_name,
        $klass,
        $fields,
        $nested_types,
        $enum_types,
        $oneof_decls,
        $options)
    {
        $this->full_name = $full_name;
        $this->klass = $klass;
        $this->fields = $fields;
        $this->nested_types = $nested_types;
        $this->enum_types = $enum_types;
        $this->oneof_decls = $oneof_decls;
        $this->options = $options;

        $number_to_fields = [];
        $json_to_fields = [];
        $name_to_fields = [];
        foreach ($fields as $field) {
            $number_to_fields[$field->getNumber()] = $field;
            $json_to_fields[$field->getJsonName()] = $field;
            $name_to_fields[$field->getName()] = $field;
        }
        $this->number_to_fields = $number_to_fields;
        $this->json_to_fields = $json_to_fields;
        $this->name_to_fields = $name_to_fields;
    }

    public function getFullName()
    {
        return $this->full_name;
    }

    public function getClass()
    {
        return $this->klass;
    }

    public function getField($index)
    {
        return $this->fields[$index];
    }

    public function getFieldCount()
    {
        return count($this->fields);
    }

    public function getNestedType($index)
    {
        return $this->nested_types[$index];
    }

    public function getNestedTypes()
    {
        return $this->nested_types;
    }

    public function getNestedTypeCount()
    {
        return count($this->nested_types);
    }

    public function getEnumType($index)
    {
        return $this->enum_types[$index];
    }

    public function getEnumTypes()
    {
        return $this->enum_types;
    }

    public function getEnumTypeCount()
    {
        return count($this->enum_types);
    }

    public function getOneofDecl($index)
    {
        return $this->oneof_decls[$index];
    }

    public function getOneofDeclCount()
    {
        return count($this->oneof_decls);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getFieldByNumber($number)
    {
        if (!isset($this->number_to_fields[$number])) {
            return NULL;
        } else {
            return $this->number_to_fields[$number];
        }
    }

    public function getFieldByJsonName($json_name)
    {
        if (!isset($this->json_to_fields[$json_name])) {
            return NULL;
        } else {
            return $this->json_to_fields[$json_name];
        }
    }

    public function getFieldByName($name)
    {
        if (!isset($this->name_to_fields[$name])) {
            return NULL;
        } else {
            return $this->name_to_fields[$name];
        }
    }

    public function crossLink($pool)
    {
        foreach ($this->fields as $field) {
            $field->crossLink($pool);
        }
        foreach ($this->nested_types as $nested_type) {
            $nested_type->crossLink($pool);
        }
    }

    public static function buildFromProto($proto, $file_proto, $containing)
    {
        $message_name_without_package  = "";
        $classname = "";
        $fullname = "";
        GPBUtil::getFullClassName(
            $proto,
            $containing,
            $file_proto,
            $message_name_without_package,
            $classname,
            $fullname);

        $fields = [];
        foreach ($proto->getField() as $field_proto) {
            $fields[] = FieldDescriptor::buildFromProto($field_proto);
        }

        // Handle nested types.
        $nested_types = [];
        foreach ($proto->getNestedType() as $nested_proto) {
            $nested_types[] = Descriptor::buildFromProto(
                $nested_proto, $file_proto, $message_name_without_package);
        }

        // Handle nested enum.
        $enums = [];
        foreach ($proto->getEnumType() as $enum_proto) {
            $enums[] = EnumDescriptor::buildFromProto(
              $enum_proto, $file_proto, $message_name_without_package);
        }

        // Handle oneof fields.
        $oneofs = [];
        foreach ($proto->getOneofDecl() as $oneof_proto) {
            $oneofs[] = OneofDescriptor::buildFromProto($oneof_proto);
        }

        return new Descriptor(
            $fullname,
            $classname,
            $fields,
            $nested_types,
            $enums,
            $oneofs,
            $proto->getOptions()
        );
    }
}
