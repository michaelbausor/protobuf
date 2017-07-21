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

use Google\Protobuf\Internal\EnumDescriptor;
use Google\Protobuf\Internal\EnumValueDescriptor;

class EnumDescriptorBuilder
{
    private $full_name;
    private $klass;
    private $values = [];

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

    public function getValues()
    {
        return $this->values;
    }

    public function value($name, $number)
    {
        $this->values[$number] = new EnumValueDescriptor($name, $number);
        return $this;
    }

    public function build()
    {
        return new EnumDescriptor($this->full_name, $this->klass, $this->values);
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

        return new EnumDescriptorBuilder($fullname, $value_arr);
    }
}
