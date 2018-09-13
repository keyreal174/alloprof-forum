<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\Fixtures\Aliases;

class_alias(ExtendsNewClass::class, ExtendsNewClass::CLASS_ALIAS);

class ExtendsNewClass extends NewClass {
    const CLASS_ALIAS = "\ExtendsOldClass";
}
