<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

use Garden\Schema\Schema;
use Garden\Web\Data;
use Garden\Web\Exception\NotFoundException;
use Garden\Web\Exception\ServerException;
use Vanilla\DateFilterSchema;
use Vanilla\ApiUtils;
use Vanilla\Community\Schemas\CategoryFragmentSchema;
use Vanilla\Formatting\Formats\RichFormat;
use Vanilla\Forum\Navigation\ForumCategoryRecordType;
use Vanilla\Models\CrawlableRecordSchema;
use Vanilla\Navigation\BreadcrumbModel;
use Vanilla\SchemaFactory;
use Vanilla\Search\SearchOptions;
use Vanilla\Search\SearchResultItem;

/**
 * API Controller for the `/discussions` resource.
 */
class DiscussionsApiController extends AbstractApiController {

    use CommunitySearchSchemaTrait;
    use \Vanilla\Formatting\FormatCompatTrait;

    /** @var CategoryModel */
    private $categoryModel;

    /** @var DiscussionModel */
    private $discussionModel;

    /** @var Schema */
    private $discussionSchema;

    /** @var Schema */
    private $discussionPostSchema;

    /** @var Schema */
    private $discussionPutCanonicalSchema;

    /** @var Schema */
    private $idParamSchema;

    /** @var UserModel */
    private $userModel;

    /** @var CommentModel */
    private $commentModel;

    /** @var TagModel */
    private $tagModel;

    /**
     * DiscussionsApiController constructor.
     *
     * @param DiscussionModel $discussionModel
     * @param UserModel $userModel
     * @param CategoryModel $categoryModel
     * @param CommentModel $commentModel
     * @param TagModel $tagModel
     */
    public function __construct(
        DiscussionModel $discussionModel,
        UserModel $userModel,
        CategoryModel $categoryModel,
        CommentModel $commentModel,
        TagModel $tagModel
    ) {
        $this->categoryModel = $categoryModel;
        $this->discussionModel = $discussionModel;
        $this->userModel = $userModel;
        $this->commentModel = $commentModel;
        $this->tagModel = $tagModel;
    }

    /**
     * Get a list of the current user's bookmarked discussions.
     *
     * @param array $query The request query.
     * @return Data
     */
    public function get_bookmarked(array $query) {
        $this->permission('Garden.SignIn.Allow');

        $in = $this->schema([
            'page:i?' => [
                'description' => 'Page number. See [Pagination](https://docs.vanillaforums.com/apiv2/#pagination).',
                'default' => 1,
                'minimum' => 1,
                'maximum' => $this->discussionModel->getMaxPages()
            ],
            'limit:i?' => [
                'description' => 'Desired number of items per page.',
                'default' => $this->discussionModel->getDefaultLimit(),
                'minimum' => 1,
                'maximum' => ApiUtils::getMaxLimit(100),
            ],
            'expand?' => ApiUtils::getExpandDefinition(['insertUser', 'lastUser', 'lastPost', 'lastPost.body', 'lastPost.insertUser', 'reactions'])
        ], 'in')->setDescription('Get a list of the current user\'s bookmarked discussions.');
        $out = $this->schema([':a' => $this->discussionSchema()], 'out');

        $query = $in->validate($query);
        [$offset, $limit] = offsetLimit("p{$query['page']}", $query['limit']);

        $rows = $this->discussionModel->get($offset, $limit, [
            'w.Bookmarked' => 1,
            'w.UserID' => $this->getSession()->UserID
        ])->resultArray();

        // Expand associated rows.
        $this->userModel->expandUsers(
            $rows,
            $this->resolveExpandFields($query, ['insertUser' => 'InsertUserID', 'lastUser' => 'LastUserID', 'lastPost.insertUser' => 'LastUserID'])
        );

        foreach ($rows as &$currentRow) {
            $currentRow = $this->normalizeOutput($currentRow, $query['expand']);
        }
        $this->expandLastCommentBody($rows, $query['expand']);

        $result = $out->validate($rows);

        $result = $this->getEventManager()->fireFilter('discussionsApiController_getOutput', $result, $this, $in, $query, $rows);

        $paging = ApiUtils::morePagerInfo($result, '/api/v2/discussions/bookmarked', $query, $in);

        return new Data($result, ['paging' => $paging]);
    }

    /**
     * Delete a discussion.
     *
     * @param int $id The ID of the discussion.
     */
    public function delete($id) {
        $this->permission('Garden.SignIn.Allow');

        $in = $this->idParamSchema()->setDescription('Delete a discussion.');
        $out = $this->schema([], 'out');

        $row = $this->discussionByID($id);
        if ($row['InsertUserID'] !== $this->getSession()->UserID) {
            $this->discussionModel->categoryPermission('Vanilla.Discussions.Delete', $row['CategoryID']);
        }
        $this->discussionModel->deleteID($id);
    }

    /**
     * Get a discussion by its numeric ID.
     *
     * @param int $id The discussion ID.
     * @throws NotFoundException if the discussion could not be found.
     * @return array
     */
    public function discussionByID($id) {
        $row = $this->discussionModel->getID($id, DATASET_TYPE_ARRAY);
        if (!$row) {
            throw new NotFoundException('Discussion');
        }
        return $row;
    }

    /**
     * Get a discussion schema with minimal add/edit fields.
     *
     * @param string $type The type of schema.
     * @return Schema Returns a schema object.
     */
    public function discussionPostSchema($type = '') {
        if ($this->discussionPostSchema === null) {
            $this->discussionPostSchema = $this->schema(
                Schema::parse([
                    'name',
                    'body',
                    'format' => new \Vanilla\Models\FormatSchema(),
                    'categoryID',
                    'closed?',
                    'sink?',
                    'pinned?',
                    'pinLocation?',
                ])->add($this->fullSchema()),
                'DiscussionPost'
            );
        }
        return $this->schema($this->discussionPostSchema, $type);
    }

    /**
     * Get a discussion set canonical url schema.
     *
     * @param string $type The type of schema.
     * @return Schema Returns a schema object.
     */
    public function discussionPutCanonicalSchema($type = '') {
        if ($this->discussionPutCanonicalSchema === null) {
            $this->discussionPutCanonicalSchema = $this->schema(
                Schema::parse([
                    'canonicalUrl',
                ])->add($this->fullSchema()),
                'DiscussionPutCanonical'
            );
        }
        return $this->schema($this->discussionPutCanonicalSchema, $type);
    }

    /**
     * Get the full discussion schema.
     *
     * @param string $type The type of schema.
     * @return Schema Returns a schema object.
     */
    public function discussionSchema($type = '') {
        if ($this->discussionSchema === null) {
            $this->discussionSchema = $this->schema($this->fullSchema(), 'Discussion');
        }
        return $this->schema($this->discussionSchema, $type);
    }

    /**
     * Get a schema instance comprised of all available discussion fields.
     *
     * @return Schema Returns a schema object.
     */
    protected function fullSchema() {
        $result = $this->discussionModel
            ->schema()
            ->merge($this->discussionModel->userDiscussionSchema())
            ->merge(Schema::parse([
                'category?' => SchemaFactory::get(CategoryFragmentSchema::class, 'CategoryFragment'),
                'recordID:i?',
            ]));
        return $result;
    }

    /**
     * Get a discussion.
     *
     * @param int $id The ID of the discussion.
     * @param array $query The request query.
     * @throws NotFoundException if the discussion could not be found.
     * @return array
     */
    public function get($id, array $query) {
        $this->permission();

        $this->idParamSchema();
        $in = $this->schema([
            'expand?' => ApiUtils::getExpandDefinition([
                'lastPost',
                'lastPost.body',
                'lastPost.insertUser',
                '-lastUser',
                '-insertUser',
                'tagIDs',
                'breadcrumbs',
            ]) // Allow addons to expand additional fields.
        ], ['DiscussionGet', 'in'])->setDescription('Get a discussion.');

        $query = $in->validate($query);
        $discussionSchema = CrawlableRecordSchema::applyExpandedSchema($this->discussionSchema(), 'discussion', $query['expand']);
        $out = $this->schema($discussionSchema, 'out');

        $this->getEventManager()->fireFilter('discussionsApiController_getFilters', $this, $id, $query);

        $row = $this->discussionByID($id);
        if (!$row) {
            throw new NotFoundException('Discussion');
        }

        $this->discussionModel->categoryPermission('Vanilla.Discussions.View', $row['CategoryID']);

        $this->userModel->expandUsers($row, ['insertUser' => 'InsertUserID', 'lastUser' => 'LastUserID']);
        $row = $this->normalizeOutput($row, $query["expand"]);
        $rows = [&$row];
        $this->expandLastCommentBody($rows, $query['expand']);

        $result = $out->validate($row);

        // Allow addons to modify the result.
        $result = $this->getEventManager()->fireFilter('discussionsApiController_getOutput', $result, $this, $in, $query, $row, true);
        return $result;
    }

    /**
     * Normalize a database record to match the Schema definition.
     *
     * @param array $dbRecord Database record.
     * @param array|string|bool $expand
     * @return array Return a Schema record.
     */
    public function normalizeOutput(array $dbRecord, $expand = []) {
        if ($this->getSession()->User) {
            $dbRecord['unread'] = $dbRecord['CountUnreadComments'] !== 0
                && ($dbRecord['CountUnreadComments'] !== true || dateCompare(val('DateFirstVisit', $this->getSession()->User), $dbRecord['DateInserted']) <= 0);
            if ($dbRecord['CountUnreadComments'] !== true && $dbRecord['CountUnreadComments'] > 0) {
                $dbRecord['countUnread'] = $dbRecord['CountUnreadComments'];
            }
        } else {
            $dbRecord['unread'] = false;
        }

        // The Category key will hold a category fragment in API responses. Ditch the default string.
        if (array_key_exists('Category', $dbRecord) && !is_array($dbRecord['Category'])) {
            unset($dbRecord['Category']);
        }

        $normalizedRow = $this->discussionModel->normalizeRow($dbRecord, $expand);

        // Fetch the crumb model lazily to prevent DI issues.
        /** @var BreadcrumbModel $breadcrumbModel */
        $breadcrumbModel =\Gdn::getContainer()->get(BreadcrumbModel::class);
        if ($this->isExpandField('breadcrumbs', $expand)) {
            $normalizedRow['breadcrumbs'] = $breadcrumbModel->getForRecord(new ForumCategoryRecordType($normalizedRow['categoryID']));
        }

        // Allow addons to hook into the normalization process.
        $options = ['expand' => $expand];
        $result = $this->getEventManager()->fireFilter(
            'discussionsApiController_normalizeOutput',
            $normalizedRow,
            $this,
            $options
        );

        return $result;
    }

    /**
     * Get a discussion's quote data.
     *
     * @param int $id The ID of the discussion.
     *
     * @return array The discussion quote data.
     *
     * @throws NotFoundException If the record with the given ID can't be found.
     * @throws \Exception Throws an exception if no session is available.
     * @throws \Vanilla\Exception\PermissionException Throws an exception if the user does not have the specified permission(s).
     * @throws \Garden\Schema\ValidationException If the output schema is configured incorrectly.
     */
    public function get_quote($id) {
        $this->permission();

        $this->idParamSchema();
        $in = $this->schema([], 'in')->setDescription('Get a discussions embed data.');
        $out = $this->schema($this->quoteSchema(), 'out');

        $discussion = $this->discussionByID($id);
        $discussion['Url'] = discussionUrl($discussion);

        $this->getEventManager()->fireFilter('discussionsApiController_getFilters', $this, $id, []);

        if ($discussion['InsertUserID'] !== $this->getSession()->UserID) {
            $this->discussionModel->categoryPermission('Vanilla.Discussions.View', $discussion['CategoryID']);
        }

        $isRich = strcasecmp($discussion['Format'], RichFormat::FORMAT_KEY) === 0;
        $discussion['bodyRaw'] = $isRich ? json_decode($discussion['Body'], true) : $discussion['Body'];
        $discussion = $this->discussionModel->fixRow($discussion);

        $this->userModel->expandUsers($discussion, ['InsertUserID']);
        $result = $out->validate($discussion);
        return $result;
    }

    /**
     * Get the schema for discussion quote data.
     *
     * @return Schema
     */
    private function quoteSchema(): Schema {
        return Schema::parse([
            'discussionID:i' => 'The ID of the discussion.',
            'name:s' => 'The title of the discussion',
            'bodyRaw:s|a' => 'The raw body of the discussion. This can be an array of rich operations or a string for other formats',
            'dateInserted:dt' => 'When the discussion was created.',
            'dateUpdated:dt|n' => 'When the discussion was last updated.',
            'insertUser' => $this->getUserFragmentSchema(),
            'url:s' => 'The full URL to the discussion.',
            'format' => new \Vanilla\Models\FormatSchema(true),
        ]);
    }

    /**
     * Get a discussion for editing.
     *
     * @param int $id The ID of the discussion.
     * @throws NotFoundException if the discussion could not be found.
     * @return array
     */
    public function get_edit($id) {
        $this->permission('Garden.SignIn.Allow');

        $this->idParamSchema()->setDescription('Get a discussion for editing.');
        $out = $this->schema(
            Schema::parse([
                'discussionID',
                'name',
                'body',
                'format' => new \Vanilla\Models\FormatSchema(true),
                'categoryID',
                'sink',
                'closed',
                'pinned',
                'pinLocation',
            ])->add($this->fullSchema()),
            ['DiscussionGetEdit', 'out']
        )->addFilter('', [\Vanilla\Formatting\Formats\RichFormat::class, 'editBodyFilter']);

        $row = $this->discussionByID($id);
        $row['Url'] = discussionUrl($row);

        $this->getEventManager()->fireFilter('discussionsApiController_getFilters', $this, $id, []);
        if ($row['InsertUserID'] !== $this->getSession()->UserID) {
            $this->discussionModel->categoryPermission('Vanilla.Discussions.Edit', $row['CategoryID']);
        }

        $result = $out->validate($row);
        $this->applyFormatCompatibility($result, 'body', 'format');
        return $result;
    }

    /**
     * Get an ID-only discussion record schema.
     *
     * @param string $type The type of schema.
     * @return Schema Returns a schema object.
     */
    public function idParamSchema($type = 'in') {
        if ($this->idParamSchema === null) {
            $this->idParamSchema = $this->schema(
                Schema::parse(['id:i' => 'The discussion ID.']),
                $type
            );
        }
        return $this->schema($this->idParamSchema, $type);
    }

    /**
     * List discussions.
     *
     * @param array $query The query string.
     * @return Data
     */
    public function index(array $query) {
        $this->permission();

        $in = $this->schema([
            'discussionID?' => \Vanilla\Schema\RangeExpression::createSchema([':int'])->setField('x-filter', ['field' => 'd.discussionID']),
            'categoryID:i?' => [
                'description' => 'Filter by a category.',
                'x-filter' => [
                    'field' => 'd.CategoryID'
                ],
            ],
            'dateInserted?' => new DateFilterSchema([
                'description' => 'When the discussion was created.',
                'x-filter' => [
                    'field' => 'd.DateInserted',
                    'processor' => [DateFilterSchema::class, 'dateFilterField'],
                ],
            ]),
            'dateUpdated?' => new DateFilterSchema([
                'description' => 'When the discussion was updated.',
                'x-filter' => [
                    'field' => 'd.DateUpdated',
                    'processor' => [DateFilterSchema::class, 'dateFilterField'],
                ],
            ]),
            'dateLastComment?' => new DateFilterSchema([
                'description' => 'When the last comment was posted.',
                'x-filter' => [
                    'field' => 'd.DateLastComment',
                    'processor' => [DateFilterSchema::class, 'dateFilterField'],
                ],
            ]),
            'type:s?' => [
                'description' => 'Filter by discussion type.',
                'x-filter' => [
                    'field' => 'd.Type'
                ],
            ],
            'followed:b' => [
                'default' => false,
                'description' => 'Only fetch discussions from followed categories. Pinned discussions are mixed in.'
            ],
            'pinned:b?',
            'pinOrder:s?' => [
                'default' => 'first',
                'enum' => ['first', 'mixed'],
            ],
            'page:i?' => [
                'description' => 'Page number. See [Pagination](https://docs.vanillaforums.com/apiv2/#pagination).',
                'default' => 1,
                'minimum' => 1
            ],
            'limit:i?' => [
                'description' => 'Desired number of items per page.',
                'default' => $this->discussionModel->getDefaultLimit(),
                'minimum' => 1,
                'maximum' => ApiUtils::getMaxLimit(),
            ],
            'sort:s?' => [
                'enum' => ApiUtils::sortEnum('dateLastComment', 'dateInserted', 'discussionID'),
            ],
            'insertUserID:i?' => [
                'description' => 'Filter by author.',
                'x-filter' => [
                    'field' => 'd.InsertUserID',
                ],
            ],
            'expand?' => ApiUtils::getExpandDefinition([
                'category',
                'insertUser',
                'lastUser',
                'lastPost',
                'raw',
                'lastPost.body',
                'lastPost.insertUser',
                'tagIDs',
                'breadcrumbs',
            ])
        ], ['DiscussionIndex', 'in'])->setDescription('List discussions.');

        $query = $in->validate($query);
        $query = $this->filterValues($query);

        $discussionSchema = CrawlableRecordSchema::applyExpandedSchema($this->discussionSchema(), 'discussion', $query['expand']);
        $out = $this->schema([':a' => $discussionSchema], 'out');

        $where = ApiUtils::queryToFilters($in, $query);

        [$offset, $limit] = offsetLimit("p{$query['page']}", $query['limit']);

        if (array_key_exists('categoryID', $where)) {
            $this->discussionModel->categoryPermission('Vanilla.Discussions.View', $where['categoryID']);
        }

        // Allow addons to update the where clause.
        $where = $this->getEventManager()->fireFilter('discussionsApiController_indexFilters', $where, $this, $in, $query);

        if ($query['followed']) {
            $where['Followed'] = true;
            $query['pinOrder'] = 'mixed';
        }

        $pinned = $query['pinned'] ?? null;
        if ($pinned === true) {
            $announceWhere = array_merge($where, ['d.Announce >' => '0']);
            $rows = $this->discussionModel->getAnnouncements($announceWhere, $offset, $limit, $query['sort'] ?? '')->resultArray();
        } else {
            $pinOrder = array_key_exists('pinOrder', $query) ? $query['pinOrder'] : null;
            [$orderField, $orderDirection] = \Vanilla\Models\LegacyModelUtils::orderFieldDirection($query['sort'] ?? '');
            if ($pinOrder == 'first') {
                $announcements = $this->discussionModel->getAnnouncements($where, $offset, $limit, $query['sort'] ?? '')->resultArray();
                $discussions = $this->discussionModel->getWhere($where, $orderField, $orderDirection, $limit, $offset, false)->resultArray();
                $rows = array_merge($announcements, $discussions);
            } else {
                $where['Announce'] = 'all';
                $rows = $this->discussionModel->getWhere($where, $orderField, $orderDirection, $limit, $offset, false)->resultArray();
            }
        }

        // Expand associated rows.
        $this->userModel->expandUsers(
            $rows,
            $this->resolveExpandFields($query, [
                'insertUser' => 'InsertUserID',
                'lastUser' => 'LastUserID',
                'lastPost.insertUser' => 'LastUserID',
            ])
        );
        if ($this->isExpandField('category', $query['expand'])) {
            $this->categoryModel->expandCategories($rows);
        }

        if ($this->isExpandField('tagIDs', $query['expand'])) {
            $this->tagModel->expandTagIDs($rows);
        }

        foreach ($rows as &$currentRow) {
            $currentRow = $this->normalizeOutput($currentRow, $query['expand']);
        }
        $this->expandLastCommentBody($rows, $query['expand']);

        $result = $out->validate($rows);

        // Allow addons to modify the result.
        $result = $this->getEventManager()->fireFilter('discussionsApiController_getOutput', $result, $this, $in, $query, $rows);

        $whereCount = count($where);
        $isWhereOptimized = (isset($where['d.CategoryID']) && ($whereCount === 1 || ($whereCount === 2 && isset($where['Announce']))));
        if ($whereCount === 0 || $isWhereOptimized) {
            $paging = ApiUtils::numberedPagerInfo($this->discussionModel->getCount($where), '/api/v2/discussions', $query, $in);
        } else {
            $paging = ApiUtils::morePagerInfo($rows, '/api/v2/discussions', $query, $in);
        }

        return new Data($result, ['paging' => $paging]);
    }

    /**
     * Update a discussion.
     *
     * @param int $id The ID of the discussion.
     * @param array $body The request body.
     * @throws NotFoundException if unable to find the discussion.
     * @return array
     */
    public function patch($id, array $body) {
        $this->permission('Garden.SignIn.Allow');

        $this->idParamSchema('in');
        $in = $this->discussionPostSchema('in')->setDescription('Update a discussion.');
        $out = $this->schema($this->discussionSchema(), 'out');

        $body = $in->validate($body, true);

        $row = $this->discussionByID($id);
        $discussionData = ApiUtils::convertInputKeys($body);
        $discussionData['DiscussionID'] = $id;
        $categoryID = $row['CategoryID'];
        if ($row['InsertUserID'] !== $this->getSession()->UserID) {
            $this->discussionModel->categoryPermission('Vanilla.Discussions.Edit', $categoryID);
        }
        if (array_key_exists('CategoryID', $discussionData) && $categoryID !== $discussionData['CategoryID']) {
            $this->discussionModel->categoryPermission('Vanilla.Discussions.Add', $discussionData['CategoryID']);
            $categoryID = $discussionData['CategoryID'];
        }

        $permissionCategoryID = self::getPermissionID($categoryID);

        $this->fieldPermission($body, 'closed', 'Vanilla.Discussions.Close', $permissionCategoryID);
        $this->fieldPermission($body, 'pinned', 'Vanilla.Discussions.Announce', $permissionCategoryID);
        $this->fieldPermission($body, 'sink', 'Vanilla.Discussions.Sink', $permissionCategoryID);

        $this->discussionModel->save($discussionData);
        $this->validateModel($this->discussionModel);

        $result = $this->discussionByID($id);
        $result = $this->normalizeOutput($result);
        return $out->validate($result);
    }

    /**
     * Get the category permission ID.
     *
     * @param int $categoryID The category ID.
     * @return int Returns the associated permission ID.
     */
    public static function getPermissionID(int $categoryID): int {
        $category = CategoryModel::categories($categoryID);
        if ($category) {
            return $category['PermissionCategoryID'];
        } else {
            return -1;
        }
    }

    /**
     * Add a discussion.
     *
     * @param array $body The request body.
     * @throws ServerException if the discussion could not be created.
     * @return array
     */
    public function post(array $body) {
        $this->permission('Garden.SignIn.Allow');

        $in = $this->discussionPostSchema('in')->setDescription('Add a discussion.');
        $out = $this->discussionSchema('out');

        $body = $in->validate($body);
        $categoryID = $body['categoryID'];
        $categoryPermissionID = self::getPermissionID($categoryID);
        $this->discussionModel->categoryPermission('Vanilla.Discussions.Add', $categoryID);
        $this->fieldPermission($body, 'closed', 'Vanilla.Discussions.Close', $categoryPermissionID);
        $this->fieldPermission($body, 'pinned', 'Vanilla.Discussions.Announce', $categoryPermissionID);
        $this->fieldPermission($body, 'sink', 'Vanilla.Discussions.Sink', $categoryPermissionID);

        $discussionData = ApiUtils::convertInputKeys($body);
        $id = $this->discussionModel->save($discussionData);
        $this->validateModel($this->discussionModel);

        if (!$id) {
            throw new ServerException('Unable to insert discussion.', 500);
        }

        $row = $this->discussionByID($id);
        $this->userModel->expandUsers($row, ['InsertUserID', 'LastUserID']);
        $row = $this->normalizeOutput($row);
        $result = $out->validate($row);
        return $result;
    }

    /**
     * Bookmark a discussion.
     *
     * @param int $id The ID of the discussion.
     * @param array $body The request body.
     * @return array
     */
    public function put_bookmark($id, array $body) {
        $this->permission('Garden.SignIn.Allow');

        $this->idParamSchema('in');
        $in = $this
            ->schema(['bookmarked:b' => 'Pass true to bookmark or false to remove bookmark.'], 'in')
            ->setDescription('Bookmark a discussion.');
        $out = $this->schema(['bookmarked:b' => 'The current bookmark value.'], 'out');

        $body = $in->validate($body);
        $row = $this->discussionByID($id);
        $bookmarked = intval($body['bookmarked']);
        $this->discussionModel->categoryPermission('Vanilla.Discussions.View', $row['CategoryID']);
        $this->discussionModel->bookmark($id, $this->getSession()->UserID, $bookmarked);

        $result = $this->discussionByID($id);
        return $out->validate($result);
    }

    /**
     * Set canonical url for a discussion.
     *
     * @param int $id The ID of the discussion.
     * @param array $body The request body.
     *       Ex: ["canonicalUrl" => "https://mydomain.com/some+path/"]
     * @throws NotFoundException If unable to find the discussion.
     * @return array
     */
    public function put_canonicalUrl($id, array $body) {
        $this->permission('Garden.SignIn.Allow');

        $this->idParamSchema('in');
        $in = $this->discussionPutCanonicalSchema('in')->setDescription('Set canonical url for a discussion.');
        $out = $this->schema($this->discussionSchema(), 'out');

        $body = $in->validate($body);

        $row = $this->discussionByID($id);
        if (!empty($row['Attributes']['CanonicalUrl'] ?? '')) {
            throw new \Garden\Web\Exception\ClientException('Canonical url already set for this discussion.', 409);
        };
        $categoryID = $row['CategoryID'];
        if ($row['InsertUserID'] !== $this->getSession()->UserID) {
            $this->discussionModel->categoryPermission('Vanilla.Discussions.Edit', $categoryID);
        }

        $attributes = $row['Attributes'] ?? [];
        $attributes['CanonicalUrl'] = $body['canonicalUrl'];
        $this->discussionModel->setProperty($id, 'Attributes', dbencode($attributes));

        $result = $this->discussionByID($id);
        $result = $this->normalizeOutput($result);
        return $out->validate($result);
    }

    /**
     * Remove canonical url for a discussion.
     *
     * @param int $id The ID of the discussion.
     * @throws NotFoundException If unable to find the discussion.
     * @return array
     */
    public function delete_canonicalUrl($id) {
        $this->permission('Garden.SignIn.Allow');

        $in =$this->schema(
            Schema::parse(['id:i' => 'The discussion ID.']),
            'in'
        );
        $out = $this->schema([], 'out');

        $row = $this->discussionByID($id);
        $categoryID = $row['CategoryID'];
        if ($row['InsertUserID'] !== $this->getSession()->UserID) {
            $this->discussionModel->categoryPermission('Vanilla.Discussions.Edit', $categoryID);
        }
        $attributes = $row['Attributes'];
        if (!empty($attributes['CanonicalUrl'] ?? '')) {
            unset($attributes['CanonicalUrl']);
            $this->discussionModel->setProperty($id, 'Attributes', dbencode($attributes));
        }
    }

    /**
     * Search discussions.
     *
     * @param array $query
     * @return Data
     */
    public function get_search(array $query) {
        $this->permission();

        $in = $this
            ->schema([
                'categoryID:i?' => 'The numeric ID of a category to limit search results to.',
                'followed:b?' => 'Limit results to those in followed categories. Cannot be used with the categoryID parameter.',
            ], 'in')
            ->merge($this->searchSchema())
            ->setDescription('Search discussions.');
        $query = $in->validate($query);

        [$offset, $limit] = offsetLimit(
            "p{$query['page']}",
            $query['limit']
        );
        $searchQuery = [
            'recordTypes' => ['discussion'],
            'query' => $query['query'],
        ];

        if (array_key_exists('categoryID', $query)) {
            $searchQuery['categoryID'] = $query['categoryID'];
        } elseif ($this->getSession()->isValid() && !empty($query['followed'])) {
            $searchQuery['followedCategories'] = true;
        }

        $results = $this->getSearchService()->search($searchQuery, new SearchOptions($offset, $limit));
        $discussionIDs = [];
        /** @var SearchResultItem $result */
        foreach ($results as $result) {
            $discussionIDs[] = $result->getRecordID();
        }

        // Hit the discussion API back for formatting.
        return $this->index([
            'discussionID' => $discussionIDs,
            'expand' => $query['expand'] ?? null,
            'limit' => $query['limit'] ?? null,
        ]);
    }

    /**
     * Expand the body of the last comment.
     *
     * @param array $rows
     * @param array|bool $expand
     */
    private function expandLastCommentBody(array &$rows, $expand): void {
        if (!$this->isExpandField('lastPost', $expand) || !$this->isExpandField('lastPost.body', $expand)) {
            return;
        }

        $commentIDs = [];
        foreach ($rows as $row) {
            $id = $row['lastPost']['commentID'] ?? null;
            if (is_int($id)) {
                $commentIDs[] = $id;
            }
        }
        if (!empty($commentIDs)) {
            $comments = $this->commentModel->getWhere(['commentID' => $commentIDs], '', 'asc', count($commentIDs))->resultArray();
            $comments = array_column($comments, null, 'CommentID');
        } else {
            $comments = [];
        }

        foreach ($rows as &$row) {
            $id = $row['lastPost']['commentID'] ?? null;
            if (isset($comments[$id])) {
                $row['lastPost']['body'] = \Gdn::formatService()->renderHTML($comments[$id]['Body'], $comments[$id]['Format']);
            } else {
                $row['lastPost']['body'] = $row['body'];
            }
        }
    }
}
