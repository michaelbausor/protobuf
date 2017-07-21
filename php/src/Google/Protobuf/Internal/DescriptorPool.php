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

class DescriptorPool
{
    private static $pool;
    // Map from message names to sub-maps, which are maps from field numbers to
    // field descriptors.
    private $class_to_desc = [];
    private $class_to_enum_desc = [];
    private $proto_to_class = [];

    public static function getGeneratedPool()
    {
        if (!isset(self::$pool)) {
            self::$pool = new DescriptorPool();
        }
        return self::$pool;
    }

    public function internalAddGeneratedFile($data)
    {
        $files = new FileDescriptorSet();
        $files->mergeFromString($data);
        $file = FileDescriptor::buildFromProto($files->getFile()[0]);
        $this->internalAdd($file->getMessageType(), $file->getEnumType());
    }

    public function internalAdd(&$messageTypes, &$enumTypes)
    {
        foreach ($messageTypes as &$desc) {
            $this->addDescriptor($desc);
        }
        unset($desc);

        foreach ($enumTypes as &$desc) {
            $this->addEnumDescriptor($desc);
        }
        unset($desc);

        foreach ($messageTypes as &$desc) {
            $desc->crossLink($this);
        }
        unset($desc);
    }

    private function addDescriptor($descriptor)
    {
        $this->proto_to_class[$descriptor->getFullName()] =
            $descriptor->getClass();
        $this->class_to_desc[$descriptor->getClass()] = $descriptor;
        foreach ($descriptor->getNestedTypes() as $nested_type) {
            $this->addDescriptor($nested_type);
        }
        foreach ($descriptor->getEnumTypes() as $enum_type) {
            $this->addEnumDescriptor($enum_type);
        }
    }

    private function addEnumDescriptor($descriptor)
    {
        $this->proto_to_class[$descriptor->getFullName()] =
            $descriptor->getClass();
        $this->class_to_enum_desc[$descriptor->getClass()] = $descriptor;
    }

    /**
     * @param string $klass PHP class name
     * @return \Google\Protobuf\Descriptor
     */
    public function getDescriptorByClassName($klass)
    {
        return $this->class_to_desc[$klass];
    }

    /**
     * @param string $klass PHP class name
     * @return \Google\Protobuf\EnumValueDescriptor
     */
    public function getEnumDescriptorByClassName($klass)
    {
        return $this->class_to_enum_desc[$klass];
    }

    public function getDescriptorByProtoName($proto)
    {
        $klass = $this->proto_to_class[$proto];
        return $this->class_to_desc[$klass];
    }

    public function getEnumDescriptorByProtoName($proto)
    {
        $klass = $this->proto_to_class[$proto];
        return $this->class_to_enum_desc[$klass];
    }
}
