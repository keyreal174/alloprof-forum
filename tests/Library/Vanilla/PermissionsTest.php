<?php
/**
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license GPLv2
 */

namespace VanillaTests\Library\Vanilla;

use Vanilla\Permissions;

class PermissionsTest extends \PHPUnit_Framework_TestCase {

    public function testAdd() {
        $permissions = new Permissions();

        $permissions->add('Vanilla.Discussions.Add', 10);
        $this->assertTrue($permissions->has('Vanilla.Discussions.Add', 10));
        $this->assertFalse($permissions->has('Vanilla.Discussions.Add'));
    }

    public function testCompileAndLoad() {
        $permissions = new Permissions();
        $exampleRows = [
            [
                'PermissionID' => 1,
                'RoleID' => 8,
                'JunctionTable' => null,
                'JunctionColumn' => null,
                'JunctionID' => null,
                'Garden.SignIn.Allow' => 1,
                'Garden.Settings.Manage' => 0,
                'Vanilla.Discussions.View' => 1
            ],
            [
                'PermissionID' => 2,
                'RoleID' => 8,
                'JunctionTable' => 'Category',
                'JunctionColumn' => 'PermissionCategoryID',
                'JunctionID' => 10,
                'Vanilla.Discussions.Add' => 1
            ]
        ];
        $permissions->compileAndLoad($exampleRows);

        $this->assertTrue($permissions->has('Garden.SignIn.Allow'));
        $this->assertTrue($permissions->has('Vanilla.Discussions.View'));
        $this->assertFalse($permissions->has('Garden.Settings.Manage'));
        $this->assertTrue($permissions->has('Vanilla.Discussions.Add', 10));
        $this->assertFalse($permissions->has('Vanilla.Discussions.Add'));
    }

    public function testHasAny() {
        $permissions = new Permissions([
            'Vanilla.Comments.Add'
        ]);

        $this->assertTrue($permissions->hasAny([
            'Vanilla.Discussions.Add',
            'Vanilla.Discussions.Edit',
            'Vanilla.Comments.Add',
            'Vanilla.Comments.Edit'
        ]));
        $this->assertFalse($permissions->hasAny([
            'Garden.Settings.Manage',
            'Garden.Community.Manage',
            'Garden.Moderation.Manage'
        ]));
    }

    public function testHasAll() {
        $permissions = new Permissions([
            'Vanilla.Discussions.Add',
            'Vanilla.Discussions.Edit',
            'Vanilla.Comments.Add',
            'Vanilla.Comments.Edit'
        ]);

        $this->assertTrue($permissions->hasAll([
            'Vanilla.Discussions.Add',
            'Vanilla.Discussions.Edit',
            'Vanilla.Comments.Add',
            'Vanilla.Comments.Edit'
        ]));
        $this->assertFalse($permissions->hasAll([
            'Vanilla.Discussions.Announce',
            'Vanilla.Discussions.Add',
            'Vanilla.Discussions.Edit',
            'Vanilla.Comments.Add',
            'Vanilla.Comments.Edit',
        ]));
    }

    public function testHas() {
        $permissions = new Permissions([
            'Vanilla.Discussions.View',
            'Vanilla.Discussions.Add' => [10]
        ]);

        $this->assertTrue($permissions->has('Vanilla.Discussions.View'));
        $this->assertFalse($permissions->has('Garden.Settings.Manage'));

        $this->assertTrue($permissions->has('Vanilla.Discussions.Add', 10));
        $this->assertFalse($permissions->has('Vanilla.Discussions.Add', 100));
    }

    public function testMerge() {
    }

    public function testOverwrite() {
    }

    public function testRemove() {
        $permissions = new Permissions([
            'Vanilla.Discussions.Add' => [10],
            'Vanilla.Discussions.Edit' => [10]
        ]);

        $permissions->remove('Vanilla.Discussions.Edit', 10);

        $this->assertTrue($permissions->has('Vanilla.Discussions.Add', 10));
        $this->assertFalse($permissions->has('Vanilla.Discussions.Edit', 10));
    }

    public function testSet() {
        $permissions = new Permissions();
        $permissions->set('Garden.SignIn.Allow', true);

        $this->assertTrue($permissions->has('Garden.SignIn.Allow'));
    }

    public function testSetPermissions() {
        $permissions = new Permissions();
        $permissions->setPermissions([
            'Garden.SignIn.Allow',
            'Vanilla.Discussions.Add',
            'Vanilla.Discussions.Edit',
            'Vanilla.Comments.Add' => [10],
            'Vanilla.Comments.Edit' => [10]
        ]);

        $this->assertTrue($permissions->has('Garden.SignIn.Allow'));
        $this->assertTrue($permissions->has('Vanilla.Discussions.Add'));
        $this->assertTrue($permissions->has('Vanilla.Discussions.Edit'));
        $this->assertTrue($permissions->has('Vanilla.Comments.Add', 10));
        $this->assertTrue($permissions->has('Vanilla.Comments.Edit', 10));
        $this->assertFalse($permissions->has('Garden.Settings.Manage'));
        $this->assertFalse($permissions->has('Vanilla.Comments.Add'));
        $this->assertFalse($permissions->has('Vanilla.Comments.Edit'));
    }
}
