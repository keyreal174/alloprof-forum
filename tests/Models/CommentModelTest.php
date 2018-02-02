<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPLv2
 */

namespace VanillaTests\Models;

use PHPUnit\Framework\TestCase;
use CommentModel;
use DiscussionModel;
use VanillaTests\SiteTestTrait;

/**
 * Test {@link CommentModel}.
 */
class CommentModelTest extends TestCase {
    use SiteTestTrait {
        setupBeforeClass as baseSetupBeforeClass;
    }

    /**
     * {@inheritdoc}
     */
    public static function setupBeforeClass() {
        self::baseSetupBeforeClass();

        // Test as an admin
        self::container()->get('Session')->start(self::$siteInfo['adminUserID']);
    }

    /**
     * Test the lookup method.
     */
    public function testLookup() {
        $commentModel = new CommentModel();
        $discussionModel = new DiscussionModel();

        $discussion = [
            'CategoryID' => 1,
            'Name' => 'Comment Lookup Test',
            'Body' => 'foo foo foo',
            'Format' => 'Text',
            'InsertUserID' => 1
        ];
        $discussionID = $discussionModel->save($discussion);

        $comment = [
            'DiscussionID' => $discussionID,
            'Body' => 'Hello world.',
            'Format' => 'Text'
        ];
        $commentID = $commentModel->save($comment);
        $this->assertNotFalse($commentID);

        $result = $commentModel->lookup(['CommentID' => $commentID] + $comment);
        $this->assertInstanceOf('Gdn_DataSet', $result);
        $this->assertEquals(1, $result->count());

        $row = $result->firstRow(DATASET_TYPE_ARRAY);
        $this->assertEquals($commentID, $row['CommentID']);
    }
}
