<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\Fixtures\Aliases;

class_alias(NewClassFromNamespace::class, NewClassFromNamespace::CLASS_ALIAS);

class NewClassFromNamespace {
    const CLASS_ALIAS = "\NS\OldClass";
}
