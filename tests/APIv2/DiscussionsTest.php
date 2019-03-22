<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\APIv2;

use CategoryModel;
use DiscussionModel;

/**
 * Test the /api/v2/discussions endpoints.
 */
class DiscussionsTest extends AbstractResourceTest {
    use TestPutFieldTrait;

    /** @var array */
    private static $categoryIDs = [];

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null, array $data = [], $dataName = '') {
        $this->baseUrl = '/discussions';

        $this->patchFields = ['body', 'categoryID', 'closed', 'format', 'name', 'pinLocation', 'pinned', 'sink'];

        parent::__construct($name, $data, $dataName);
    }

    /**
     * {@inheritdoc}
     */
    public function record() {
        $record = $this->record;
        $record += ['categoryID' => reset(self::$categoryIDs), 'name' => __CLASS__];
        return $record;
    }

    /**
     * {@inheritdoc}
     */
    protected function modifyRow(array $row) {
        $row = parent::modifyRow($row);

        if (array_key_exists('categoryID', $row) && !in_array($row['categoryID'], self::$categoryIDs)) {
            throw new \Exception('Provided category ID ('.$row['categoryID'].') was not associated with a valid test category');
        }

        $row['closed'] = !$row['closed'];
        $row['pinned'] = !$row['pinned'];
        if ($row['pinned']) {
            $row['pinLocation'] = $row['pinLocation'] == 'category' ? 'recent' : 'category';
        } else {
            $row['pinLocation'] = null;
        }
        $row['sink'] = !$row['sink'];

        return $row;
    }

    /**
     * {@inheritdoc}
     */
    public function providePutFields() {
        $fields = [
            'bookmark' => ['bookmark', true, 'bookmarked'],
        ];
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public static function setupBeforeClass() {
        parent::setupBeforeClass();

        /** @var CategoryModel $categoryModel */
        $categoryModel = self::container()->get('CategoryModel');
        $categories = ['Test Category A', 'Test Category B', 'Test Category C'];
        foreach ($categories as $category) {
            $urlCode = preg_replace('/[^A-Z0-9]+/i', '-', strtolower($category));
            self::$categoryIDs[] = $categoryModel->save([
                'Name' => $category,
                'UrlCode' => $urlCode,
                'InsertUserID' => self::$siteInfo['adminUserID']
            ]);
        }
    }

    public function setUp() {
        parent::setUp();
        DiscussionModel::categoryPermissions(false, true);
    }
    /**
     * Verify a bookmarked discussion shows up under /discussions/bookmarked.
     */
    public function testBookmarked() {
        $row = $this->testPost();
        $rowID = $row['discussionID'];
        $this->api()->put("{$this->baseUrl}/{$row[$this->pk]}/bookmark", ['bookmarked' => 1]);
        $bookmarked = $this->api()->get("{$this->baseUrl}/bookmarked")->getBody();
        $discussionIDs = array_column($bookmarked, 'discussionID');
        $this->assertContains($rowID, $discussionIDs);
    }

    /**
     * Test getting a list of discussions from followed categories.
     */
    public function testIndexFollowed() {
        // Make sure we're starting from scratch.
        $preFollow = $this->api()->get($this->baseUrl, ['followed' => true])->getBody();
        $this->assertEmpty($preFollow);

        // Create a new category to follow.
        $category = $this->api()->post("categories", [
            'name' => __FUNCTION__,
            'urlcode' => __FUNCTION__
        ]);
        $testCategoryID = $category['categoryID'];
        $this->api()->put("categories/{$testCategoryID}/follow", ['followed' => true]);

        // Add some discussions
        $totalDiscussions = 3;
        $record = $this->record();
        $record['categoryID'] = $testCategoryID;
        for ($i = 1; $i <= $totalDiscussions; $i++) {
            $this->testPost($record);
        }

        // See if we have any discussions.
        $postFollow = $this->api()->get($this->baseUrl, ['followed' => true])->getBody();
        $this->assertCount($totalDiscussions, $postFollow);

        // Make sure discussions are only from the followed category.
        $categoryIDs = array_unique(array_column($postFollow, 'categoryID'));
        $this->assertCount(1, $categoryIDs);
        $this->assertEquals($testCategoryID, $categoryIDs[0]);
    }

    /**
     * Test PATCH /discussions/<id> with a a single field update.
     *
     * @param string $field The name of the field to patch.
     * @dataProvider providePatchFields
     */
    public function testPatchSparse($field) {
        // pinLocation doesn't do anything on its own, it requires pinned. It's not a good candidate for a single-field sparse PATCH.
        if ($field == 'pinLocation') {
            $this->assertTrue(true);
            return;
        }

        parent::testPatchSparse($field);
    }

    /**
     * Test PUT /discussions/{id}/canonical-url when not set
     */
    public function testPutCanonicalUrl() {
        $row = $this->testPost();
        $url = '/canonical/url/test';
        $discussion = $this->api()->put($this->baseUrl.'/'.$row['discussionID'].'/canonical-url', ['canonicalUrl' => $url])->getBody();
        $this->assertArrayHasKey('canonicalUrl', $discussion);
        $this->assertEquals($url, $discussion['canonicalUrl']);
    }

    /**
     * Test PUT /discussions/{id}/canonical-url when already set up
     */
    public function testOverwriteCanonicalUrl() {
        $row = $this->testPost();
        $url = '/canonical/url/test';
        $discussion = $this->api()->put($this->baseUrl.'/'.$row['discussionID'].'/canonical-url', ['canonicalUrl' => $url])->getBody();
        $this->assertArrayHasKey('canonicalUrl', $discussion);
        $this->assertEquals($url, $discussion['canonicalUrl']);

        $this->expectException(\Garden\Web\Exception\ClientException::class);
        $this->api()->put($this->baseUrl.'/'.$row['discussionID'].'/canonical-url', ['canonicalUrl' => $url.'overwrite']);
    }

    /**
     * Test DELETE /discussions/{id}/canonical-url
     */
    public function testDeleteCanonicalUrl() {
        $row = $this->testPost();
        $url = '/canonical/url/test';
        $discussion = $this->api()->put($this->baseUrl.'/'.$row['discussionID'].'/canonical-url', ['canonicalUrl' => $url])->getBody();
        $response = $this->api()->delete($this->baseUrl.'/'.$row['discussionID'].'/canonical-url');

        $this->assertEquals('204 No Content', $response->getStatus());

        $discussion = $response->getBody();
        $this->assertTrue(empty($discussion));

        $discussion = $this->api()->get($this->baseUrl.'/'.$row['discussionID'])->getBody();
        $this->assertNotEquals($url, $discussion['canonicalUrl']);
        $this->assertEquals($discussion['url'], $discussion['canonicalUrl']);
    }
}
