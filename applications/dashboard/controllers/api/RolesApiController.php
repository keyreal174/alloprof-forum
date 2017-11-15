<?php
/**
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license GPLv2
 */

use Garden\Schema\Schema;
use Garden\Web\Data;
use Garden\Web\Exception\NotFoundException;
use Garden\Web\Exception\ServerException;
use Vanilla\Utility\CapitalCaseScheme;
use Vanilla\Utility\CamelCaseScheme;

/**
 * API Controller for the `/roles` resource.
 */
class RolesApiController extends AbstractApiController {

    /** Maximum number of permission rows that can be displayed before an error is reported. */
    const MAX_PERMISSIONS = 100;

    /** @var CamelCaseScheme */
    private $camelCaseScheme;

    /** @var CapitalCaseScheme */
    private $caseScheme;

    /** @var CategoryModel */
    private $categoryModel;

    /** @var array Groups of permissions that can be consolidated into one. */
    private $consolidatedPermissions = [
        'discussions.moderate' => ['discussions.announce', 'discussions.close', 'discussions.sink'],
        'discussions.manage' => ['discussions.delete', 'discussions.edit']
    ];

    /** @var array Permissions that have been deprecated and should no longer be used. */
    private $deprecatedPermissions = [
        'Garden.Activity.Delete',
        'Garden.Activity.View',
        'Garden.SignIn.Allow',
        'Garden.Curation.Manage',
        'Vanilla.Approval.Require',
        'Vanilla.Comments.Me'
    ];

    /** @var Schema */
    private $idParamSchema;

    /** @var PermissionModel */
    private $permissionModel;

    /** @var array A static mapping of updated permission names. */
    private $renamedPermissions = [
        'Conversations.Moderation.Manage' => 'conversations.moderate',
        'Email.Comments.Add' => 'comments.email',
        'Email.Conversations.Add' => 'conversations.email',
        'Email.Discussions.Add' => 'discussions.email',
        'Garden.Moderation.Manage' => 'community.moderate',
        'Garden.NoAds.Allow' => 'noAds.use',
        'Garden.Settings.Manage' => 'site.manage',
        'Garden.Users.Approve' => 'applicants.manage',
        'Groups.Group.Add' => 'groups.add',
        'Groups.Moderation.Manage' => 'groups.moderate',
        'Reputation.Badges.Give' => 'badges.moderate',
        'Vanilla.Tagging.Add' => 'tags.add'
    ];

    /** @var array These permissions should not be renamed. */
    private $fixedPermissions = [
        'Reactions.Negative.Add',
        'Reactions.Positive.Add'
    ];

    /** @var bool Have all permissions been loaded into $renamedPermissions? */
    private $permissionsLoaded = false;

    /** @var RoleModel */
    private $roleModel;

    /** @var Schema */
    private $rolePostSchema;

    /** @var Schema */
    private $roleSchema;

    /**
     * RolesApiController constructor.
     *
     * @param RoleModel $roleModel
     * @param PermissionModel $permissionModel
     */
    public function __construct(RoleModel $roleModel, PermissionModel $permissionModel, CategoryModel $categoryModel) {
        $this->roleModel = $roleModel;
        $this->permissionModel = $permissionModel;
        $this->categoryModel = $categoryModel;
        $this->caseScheme = new CapitalCaseScheme();
        $this->camelCaseScheme = new CamelCaseScheme();
    }

    /**
     * Collapse multiple permissions down into a single one, where possible.
     *
     * @param array $permissions
     * @return array
     */
    private function consolidatePermissions(array $permissions) {
        $result = $permissions;

        foreach ($this->consolidatedPermissions as $name => $perms) {
            $pass = 0;
            $total = count($perms);
            foreach ($perms as $currentPerm) {
                if (!array_key_exists($currentPerm, $permissions)) {
                    // If a key isn't present, assume this is the wrong permission type (e.g. global, category).
                    continue 2;
                } elseif ($permissions[$currentPerm]) {
                    $pass++;
                }
            }

            if ($pass == $total) {
                $val = true;
            } elseif ($pass == 0) {
                $val = false;
            } else {
                $val = null;
            }

            // If we had all or none of the child permissions, remove them. Only include the parent.
            if ($val !== null) {
                foreach ($perms as $currentPerm) {
                    unset($result[$currentPerm]);
                }
            }

            $result[$name] = $val;
            unset($currentPerm, $pass);
        }

        return $result;
    }

    /**
     * Delete a role.
     *
     * @param int $id The ID of the role.
     */
    public function delete($id) {
        $this->permission('Garden.Settings.Manage');

        $in = $this->idParamSchema()->setDescription('Delete a role.');
        $out = $this->schema([], 'out');

        $this->roleByID($id);
        $this->roleModel->deleteID($id);
    }

    /**
     * Simplify the format of a permissions array.
     *
     * @param array $global Global permissions.
     * @param array $categories Category-specific permissions.
     * @return array
     */
    private function formatPermissions(array $global, array $categories) {
        $result = [];

        /**
         * Format an array of permission names. Convert names as necessary and cast values to boolean.
         *
         * @param array $perms
         * @return array
         */
        $format = function(array $perms) {
            $result = [];

            foreach ($perms as $name => $val) {
                if ($this->isPermissionDeprecated($name)) {
                    // Deprecated? Don't need it.
                    continue;
                }

                $name = $this->renamePermission($name);
                $result[$name] = (bool)$val;
            }

            $result = $this->consolidatePermissions($result);

            ksort($result);
            return $result;
        };

        $result[] = [
            'type' => 'global',
            'permissions' => $format($global)
        ];

        foreach ($categories as $cat) {
            // Default category (-1) permissions now fall under an ID of zero (0).
            $catPerms = [
                'id' => $cat['JunctionID'] == -1 ? 0 : $cat['JunctionID'],
                'type' => 'category'
            ];

            // Cleanup non-permission values from the row.
            unset($cat['Name'], $cat['JunctionID'], $cat['JunctionTable'], $cat['JunctionColumn']);

            $catPerms['permissions'] = $format($cat);
            $result[] = $catPerms;
        }

        return $result;
    }

    /**
     * Get a schema instance comprised of all available role fields.
     *
     * @return Schema Returns a schema object.
     */
    protected function fullSchema() {
        $schema = Schema::parse([
            'roleID:i' => 'ID of the role.',
            'name:s' => 'Name of the role.',
            'description:s|n' => [
                'description' => 'Description of the role.',
                'minLength' => 0
            ],
            'type:s|n' => [
                'description' => 'Default type of this role.',
                'minLength' => 0
            ],
            'deletable:b' => 'Is the role deletable?',
            'canSession:b' => 'Can users in this role start a session?',
            'personalInfo:b' => 'Is membership in this role personal information?',
            'permissions?' => $this->getPermissionsFragment()
        ]);
        return $schema;
    }

    /**
     * Get a single role.
     *
     * @param int $id The ID of the role.
     * @throws NotFoundException if unable to find the role.
     * @return array
     */
    public function get($id, array $query) {
        $this->permission('Garden.Settings.Manage');

        //$this->idParamSchema()->setDescription('Get a role.');
        $in = $this->schema([
            'expand?' => $this->getExpandFragment(['permissions'])
        ], 'in');
        $out = $this->schema($this->roleSchema(), 'out');

        $query = $in->validate($query);
        $query += ['expand' => false];

        $row = $this->roleByID($id);
        $this->prepareRow($row, $query['expand']);

        $result = $out->validate($row);
        return $result;
    }

    /**
     * Get a role for editing.
     *
     * @param int $id The ID of the role.
     * @throws NotFoundException if unable to find the role.
     * @return array
     */
    public function get_edit($id) {
        $this->permission('Garden.Settings.Manage');

        $editFields = ['roleID', 'name', 'description', 'type', 'deletable', 'canSession', 'personalInfo'];
        $in = $this->idParamSchema()->setDescription('Get a role for editing.');
        $out = $this->schema(Schema::parse($editFields)->add($this->fullSchema()), 'out');

        $row = $this->roleByID($id);

        $result = $out->validate($row);
        return $result;
    }

    /**
     * Get an array of role permissions, formatted for the API.
     *
     * @param int $roleID
     * @return array
     */
    private function getFormattedPermissions($roleID) {
        $global = $this->permissionModel->getGlobalPermissions($roleID);
        unset($global['PermissionID']);
        $category = $this->permissionModel->getJunctionPermissions(
            ['RoleID' => $roleID],
            'Category'
        );
        $result = $this->formatPermissions($global, $category);
        return $result;
    }

    /**
     * Given a permission name, lookup its legacy equivalent.
     *
     * @param string $permission The new, shortened permission name.
     * @return string|bool
     */
    private function getLegacyPermission($permission) {
        $this->loadAllPermissions();
        $result = array_search($permission, $this->renamedPermissions);
        return $result;
    }

    private function getPermissionsFragment() {
        static $permissionsFragment;

        if ($permissionsFragment === null) {
            $permissionsFragment = Schema::parse([
                ':a' => [
                    'id:i?',
                    'type:s' => ['enum' => ['global', 'category']],
                    'permissions:o'
                ]
            ]);
        }

        return $permissionsFragment;
    }

    /**
     * Get an ID-only role record schema.
     *
     * @param string $type The type of schema.
     * @return Schema Returns a schema object.
     */
    public function idParamSchema($type = 'in') {
        if ($this->idParamSchema === null) {
            $this->idParamSchema = $this->schema(
                Schema::parse(['id:i' => 'The role ID.']),
                $type
            );
        }
        return $this->schema($this->idParamSchema, $type);
    }

    /**
     * List roles.
     *
     * @return array
     */
    public function index(array $query) {
        $this->permission('Garden.Settings.Manage');

        $in = $this->schema([
            'expand?' => $this->getExpandFragment(['permissions'])
        ], 'in')->setDescription('List roles.');
        $out = $this->schema([':a' => $this->roleSchema()], 'out');

        $query = $in->validate($query);
        $query += ['expand' => false];

        $rows = $this->roleModel->getWithRankPermissions()->resultArray();
        foreach ($rows as &$row) {
            $this->prepareRow($row, $query['expand']);
        }

        $result = $out->validate($rows);
        return $result;
    }

    /**
     * Determine if a permission slug is deprecated.
     *
     * @param string $permission
     * @return bool
     */
    private function isPermissionDeprecated($permission) {
        $result = in_array($permission, $this->deprecatedPermissions);
        return $result;
    }

    /**
     * Tweak the data in a role row in a standard way.
     *
     * @param array $row
     */
    protected function prepareRow(array &$row, $expand = []) {
        if (array_key_exists('RoleID', $row)) {
            $roleID = $row['RoleID'];
            if ($this->isExpandField('permissions', $expand)) {
                $permissionCount = $this->permissionModel
                    ->getWhere(['RoleID' => $roleID], '', 'asc', self::MAX_PERMISSIONS + 1)
                    ->count();
                if ($permissionCount > self::MAX_PERMISSIONS) {
                    throw new ServerException('There are too many permissions to display.', 416);
                }
                $row['permissions'] = $this->getFormattedPermissions($roleID);
            }
        }
    }

    /**
     * Update a role.
     *
     * @param int $id The ID of the role.
     * @param array $body The request body.
     * @throws NotFoundException if unable to find the role.
     * @return array
     */
    public function patch($id, array $body) {
        $this->permission('Garden.Settings.Manage');

        $this->idParamSchema('in');
        $in = $this->rolePostSchema('in')->setDescription('Update a role.');
        $out = $this->roleSchema('out');

        $body = $in->validate($body, true);
        // If a row associated with this ID cannot be found, a "not found" exception will be thrown.
        $this->roleByID($id);
        $roleData = $this->caseScheme->convertArrayKeys($body);
        $roleData['RoleID'] = $id;
        $this->roleModel->save($roleData);
        $this->validateModel($this->roleModel);
        $row = $this->roleByID($id);

        if (array_key_exists('permissions', $body)) {
            $this->savePermissions($id, $body['permissions']);
        }

        $result = $out->validate($row);
        return $result;
    }

    /**
     * Update permissions on a role.
     *
     * @param $id
     * @param $body
     * @return array
     */
    public function patch_permissions($id, array $body) {
        $this->permission('Garden.Settings.Manage');
        $this->roleByID($id);

        $in = $this->schema($this->getPermissionsFragment(), 'in');
        $out = $this->schema([], 'out');

        $body = $in->validate($body);
        $this->savePermissions($id, $body);

        $result = $this->getFormattedPermissions($id);
        return $result;
    }

    /**
     * Add a role.
     *
     * @param array $body The request body.
     * @throws ServerException if the role could not be added.
     * @return Data
     */
    public function post(array $body) {
        $this->permission('Garden.Settings.Manage');

        $in = $this->rolePostSchema()->setDescription('Add a role.');
        $out = $this->schema($this->roleSchema(), 'out');

        $body = $in->validate($body);

        $roleData = $this->caseScheme->convertArrayKeys($body);
        $id = $this->roleModel->save($roleData);
        $this->validateModel($this->roleModel);

        if (!$id) {
            throw new ServerException('Unable to add role.', 500);
        }

        if (array_key_exists('permissions', $body)) {
            $this->savePermissions($id, $body['permissions']);
        }

        $row = $this->roleByID($id);
        $this->prepareRow($row);

        $result = $out->validate($row);
        return new Data($result, 201);
    }

    /**
     * Fill the $renamedPermissions property with all known permissions.
     */
    private function loadAllPermissions() {
        if ($this->permissionsLoaded !== true) {
            $permissions = array_keys($this->permissionModel->permissionColumns());
            unset($permissions[array_search('PermissionID', $permissions)]);

            foreach ($permissions as $permission) {
                if (!in_array($permissions, $this->deprecatedPermissions)) {
                    // This function will cache a copy of the renamed permission in the property.
                    $this->renamePermission($permission);
                }
            }

            $this->permissionsLoaded = true;
        }
    }

    /**
     * Rename a legacy Vanilla permission slug.
     *
     * @param string $permission
     * @return string
     */
    private function renamePermission($permission) {
        if (array_key_exists($permission, $this->renamedPermissions)) {
            // Already got a mapping for this permission? Go ahead and use it.
            $result = $this->renamedPermissions[$permission];
        } else {
            // Time to format the permission name.
            $segments = explode('.', $permission);

            // Pop the application off the top, if it seems safe to do so.
            if (!in_array($permission, $this->fixedPermissions) && count($segments) == 3) {
                unset($segments[0]);
            }

            foreach ($segments as &$seg) {
                $seg = $this->camelCaseScheme->convert($seg);
            }

            // Cache the renamed permission for this request.
            $result = implode('.', $segments);
            $this->renamedPermissions[$permission] = $result;
        }

        return $result;
    }

    /**
     * Get a role by its numeric ID.
     *
     * @param int $id The role ID.
     * @throws NotFoundException if the role could not be found.
     * @return array
     */
    public function roleByID($id) {
        $row = $this->roleModel->getID($id, DATASET_TYPE_ARRAY);
        if (!$row) {
            throw new NotFoundException('Role');
        }
        return $row;
    }

    /**
     * Get a role schema with minimal add/edit fields.
     *
     * @param string $type The type of schema.
     * @return Schema Returns a schema object.
     */
    public function rolePostSchema($type = '') {
        if ($this->rolePostSchema === null) {
            $fields = ['name', 'description?', 'type?', 'deletable?', 'canSession?', 'personalInfo?'];
            $this->rolePostSchema = $this->schema(
                Schema::parse($fields)->add($this->fullSchema()),
                'RolePost'
            );
            // garden-schema has an issue with merging nested schemas using Schema::add. This is a way around that for now.
            $this->rolePostSchema->merge(Schema::parse(['permissions?' => $this->getPermissionsFragment()]));
        }
        return $this->schema($this->rolePostSchema, $type);
    }

    /**
     * Get the full role schema.
     *
     * @param string $type The type of schema.
     * @return Schema Returns a schema object.
     */
    public function roleSchema($type = '') {
        if ($this->roleSchema === null) {
            $this->roleSchema = $this->schema($this->fullSchema(), 'Role');
        }
        return $this->schema($this->roleSchema, $type);
    }

    /**
     * Save a role's permissions.
     *
     * @param int $roleID
     * @param array $rows
     */
    private function savePermissions($roleID, array $rows) {
        foreach ($rows as &$row) {
            if (array_key_exists('permissions', $row)) {
                foreach ($row['permissions'] as $perm => $val) {
                    if (array_key_exists($perm, $this->consolidatedPermissions)) {
                        $expanded = array_fill_keys($this->consolidatedPermissions[$perm], (bool)$val);
                        $row['permissions'] = array_merge($row['permissions'], $expanded);
                        unset($row['permissions'][$perm]);
                    }
                }
            }
        }

        $permissions = $this->normalizePermissions($rows, $roleID);
        foreach ($permissions as $perm) {
            // The category model has its own special permission saving routine.
            if (array_key_exists('JunctionTable', $perm) && $perm['JunctionTable'] == 'Category' && $perm['JunctionID'] > 0) {
                $this->categoryModel->save([
                    'CategoryID' => $perm['JunctionID'],
                    'CustomPermissions' => true,
                    'Permissions' => [$perm]
                ]);
            } else {
                $this->permissionModel->save($perm);
            }
        }
    }

    /**
     * Given an array of permissions in the API format, alter it to be compatible with the permissions model.
     *
     * @param array $rows
     * @param int $roleID
     * @return array
     */
    private function normalizePermissions(array $rows, $roleID) {
        // Grab allowed global permissions.
        $global = $this->permissionModel->getGlobalPermissions($roleID);
        unset($global['PermissionID']);
        $global = array_keys($global);

        // Grab allowed category permissions.
        $category = $this->permissionModel->getJunctionPermissions(
            ['JunctionID' => -1],
            'Category'
        );
        $category = array_pop($category);
        unset($category['RoleID'], $category['Name'], $category['JunctionTable'], $category['JunctionColumn'], $category['JunctionID']);
        $category = array_keys($category);

        $result = [];
        foreach ($rows as $row) {
            if (!array_key_exists('type', $row)) {
                throw new InvalidArgumentException('The type property could not be found when setting permissions.');
            }
            if (!array_key_exists('permissions', $row)) {
                throw new InvalidArgumentException('The permissions property could not be found when setting permissions.');
            }

            $type = $row['type'];
            $id = array_key_exists('id', $row) ? $row['id'] : false;
            $dbRow = ['RoleID' => $roleID];

            // Ensure the permission names are legitimate and valid for their type.
            foreach ($row['permissions'] as $permission => $value) {
                $legacy = $this->getLegacyPermission($permission);
                if ($legacy === false) {
                    throw new InvalidArgumentException("Unknown permission: {$permission}");
                }
                if ($type === 'global' && !in_array($legacy, $global)) {
                    throw new InvalidArgumentException("Invalid global permission: {$legacy}.");
                } elseif ($type === 'category' && !in_array($legacy, $category)) {
                    throw new InvalidArgumentException("Invalid category permission: {$legacy}.");
                }

                $dbRow[$legacy] = (bool)$value;
            }

            // The API uses 0 for default category permissions. Revert that.
            if ($type === 'category' && $id === 0) {
                $id = -1;
            }

            if ($type === 'category') {
                if (filter_var($id, FILTER_VALIDATE_INT) === false || ($id < 0 && $id !== -1)) {
                    throw new InvalidArgumentException('Category permissions must have a valid ID.');
                }
                $dbRow['JunctionTable'] = 'Category';
                $dbRow['JunctionColumn'] = 'PermissionCategoryID';
                $dbRow['JunctionID'] = $id;
            }

            $result[] = $dbRow;
        }

        return $result;
    }
}
