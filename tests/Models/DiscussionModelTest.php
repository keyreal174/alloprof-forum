<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\Models;

use CategoryModel;
use DiscussionModel;
use Garden\EventManager;
use Gdn;
use PHPUnit\Framework\TestCase;
use Vanilla\Community\Events\DiscussionEvent;
use VanillaTests\APIv2\TestSortingTrait;
use VanillaTests\ExpectErrorTrait;
use VanillaTests\SiteTestTrait;

/**
 * Some basic tests for the `DiscussionModel`.
 */
class DiscussionModelTest extends TestCase {
    use SiteTestTrait, ExpectErrorTrait, TestDiscussionModelTrait;

    /** @var DiscussionEvent */
    private $lastEvent;

    /**
     * @var \DateTimeImmutable
     */
    private $now;

    /**
     * @var \Gdn_Session
     */
    private $session;

    /**
     * A test listener that increments the counter.
     *
     * @param TestEvent $e
     * @return TestEvent
     */
    public function handleDiscussionEvent(DiscussionEvent $e): DiscussionEvent {
        $this->lastEvent = $e;
        return $e;
    }

    /**
     * Get a new model for each test.
     */
    public function setUp(): void {
        parent::setUp();

        $this->setupTestDiscussionModelTrait();
        $this->now = new \DateTimeImmutable();
        $this->session = Gdn::session();
        $this->backupSession();

        // Make event testing a little easier.
        $this->container()->setInstance(self::class, $this);
        $this->lastEvent = null;
        /** @var EventManager */
        $eventManager = $this->container()->get(EventManager::class);
        $eventManager->unbindClass(self::class);
        $eventManager->addListenerMethod(self::class, "handleDiscussionEvent");
    }

    /**
     * Restore the session after tests.
     */
    public function tearDown(): void {
        parent::tearDown();
        $this->restoreSession();
    }

    /**
     * An empty archive date should be null.
     */
    public function testArchiveDateEmpty() {
        $this->discussionModel->setArchiveDate('');
        $this->assertNull($this->discussionModel->getArchiveDate());
    }

    /**
     * A date expression is valid.
     */
    public function testDayInPast() {
        $this->discussionModel->setArchiveDate('-3 days');
        $this->assertLessThan($this->now, $this->discussionModel->getArchiveDate());
    }

    /**
     * A future date expression gets flipped to the past.
     */
    public function testDayFlippedToPast() {
        $this->discussionModel->setArchiveDate('3 days');
        $this->assertLessThan($this->now, $this->discussionModel->getArchiveDate());
    }

    /**
     * An invalid archive date should throw an exception.
     */
    public function testInvalidArchiveDate() {
        $this->expectException(\Exception::class);

        $this->discussionModel->setArchiveDate('dnsfids');
    }

    /**
     * Test `DiscussionModel::isArchived()`.
     *
     * @param string $archiveDate
     * @param string|null $dateLastComment
     * @param bool $expected
     * @dataProvider provideIsArchivedTests
     */
    public function testIsArchived(string $archiveDate, ?string $dateLastComment, bool $expected) {
        $this->discussionModel->setArchiveDate($archiveDate);
        $actual = $this->discussionModel->isArchived($dateLastComment);
        $this->assertSame($expected, $actual);
    }

    /**
     * An invalid date should return a warning.
     */
    public function testIsArchivedInvalidDate() {
        $this->discussionModel->setArchiveDate('2019-10-26');

        $this->runWithExpectedError(function () {
            $actual = $this->discussionModel->isArchived('fldjsjs');
            $this->assertFalse($actual);
        }, self::assertErrorNumber(E_USER_WARNING));
    }

    /**
     * Provide some tests for `DiscussionModel::isArchived()`.
     *
     * @return array
     */
    public function provideIsArchivedTests(): array {
        $r = [
            ['2000-01-01', '2019-10-26', false],
            ['2000-01-01', '1999-12-31', true],
            ['2001-01-01', '2001-01-01', false],
            ['', '1999-01-01', false],
            ['2001-01-01', null, false],
        ];

        return $r;
    }


    /**
     * Test canClose() where Admin is false and user has CloseOwn permission.
     */
    public function testCanCloseAdminFalseCloseOwnTrue() {
        $this->session->UserID = 123;
        $this->session->getPermissions()->set('Vanilla.Discussions.CloseOwn', $this->session->UserID);
        $this->session->getPermissions()->setAdmin(false);
        $discussion = [
            'DiscussionID' => 0,
            'CategoryID' => 1,
            'Name' => 'test',
            'Body' => 'discuss',
            'InsertUserID' => 123
        ];
        $actual = DiscussionModel::canClose($discussion);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    /**
     * Test canClose() where Admin is false and user has CloseOwn permission but user did not start the discussion.
     */
    public function testCanCloseCloseOwnTrueNotOwn() {
        $this->session->UserID = 123;
        $this->session->getPermissions()->set('Vanilla.Discussions.CloseOwn', true);
        $this->session->getPermissions()->setAdmin(false);
        $discussion = [
            'DiscussionID' => 0,
            'CategoryID' => 1,
            'Name' => 'test',
            'Body' => 'discuss',
            'InsertUserID' => 321
        ];
        $actual = DiscussionModel::canClose($discussion);
        $expected = false;
        $this->assertSame($expected, $actual);
    }

    /**
     * Test canClose() with discussion already closed and user didn't start the discussion.
     */
    public function testCanCloseCloseIsClosed() {
        $this->session->UserID = 123;
        $this->session->getPermissions()->set('Vanilla.Discussions.CloseOwn', $this->session->UserID);
        $this->session->getPermissions()->setAdmin(false);
        $discussion = [
            'DiscussionID' => 0,
            'CategoryID' => 1,
            'Name' => 'test',
            'Body' => 'discuss',
            'InsertUserID' => 321,
            'Closed' => true,
            'Attributes' => ['ClosedByUserID' => 321]
        ];
        $actual = DiscussionModel::canClose($discussion);
        $expected = false;
        $this->assertSame($expected, $actual);
    }

    /**
     * Test canClose() where Admin is true.
     */
    public function testCanCloseAdminTrue() {
        $this->session->UserID = 123;
        $discussion = ['DiscussionID' => 0, 'CategoryID' => 1, 'Name' => 'test', 'Body' => 'discuss', 'InsertUserID' => 123];
        $actual = DiscussionModel::canClose($discussion);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    /**
     * Test canClose() with discussion object.
     */
    public function testCanCloseDiscussionObject() {
        $this->session->UserID = 123;
        $discussion = new \stdClass();
        $discussion->DiscussionID = 0;
        $discussion->CategoryID = 1;
        $discussion->Name = 'test';
        $discussion->Body = 'discuss';
        $discussion->InsertUserID = 123;
        $actual = DiscussionModel::canClose($discussion);
        $expected = true;
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests for maxDate().
     */

    /**
     * $dateOne > $dateTwo
     */
    public function testMaxDateDateOneGreater() {
        $dateOne = '2020-01-09 16:22:42';
        $dateTwo = '2019-12-02 21:55:40';
        $expected = $dateOne;
        $actual = DiscussionModel::maxDate($dateOne, $dateTwo);
        $this->assertSame($expected, $actual);
    }

    /**
     * $dateTwo > $dateOne
     */
    public function testMaxDateDateTwoGreater() {
        $dateOne = '2019-12-02 21:55:40';
        $dateTwo = '2020-01-09 16:22:42';
        $expected = $dateTwo;
        $actual = DiscussionModel::maxDate($dateOne, $dateTwo);
        $this->assertSame($expected, $actual);
    }

    /**
     * $dateOne is null
     */
    public function testMaxDateDateOneNull() {
        $dateOne = null;
        $dateTwo = '2020-01-09 16:22:42';
        $expected = $dateTwo;
        $actual = DiscussionModel::maxDate($dateOne, $dateTwo);
        $this->assertSame($expected, $actual);
    }

    /**
     * $dateTwo is null
     */
    public function testMaxDateDateTwoNull() {
        $dateOne = '2020-01-09 16:22:42';
        $dateTwo = null;
        $expected = $dateOne;
        $actual = DiscussionModel::maxDate($dateOne, $dateTwo);
        $this->assertSame($expected, $actual);
    }

    /**
     * Both dates are null
     */
    public function testMaxDateWithTwoNullValues() {
        $dateOne = null;
        $dateTwo = null;
        $expected = null;
        $actual = DiscussionModel::maxDate($dateOne, $dateTwo);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests for calculateWatch().
     *
     * @param object|array $testDiscussionArray Data to plug into discussion object.
     * @param int $testLimit Max number to get.
     * @param int $testOffset Number to skip.
     * @param int $testTotalComments Total in entire discussion (hard limit).
     * @param string|null $testMaxDateInserted The most recent insert date of the viewed comments.
     * @param array $expected The expected result.
     * @dataProvider provideTestCalculateWatchArrays
     * @throws \Exception Throws an exception if given an invalid timestamp.
     */
    public function testCalculateWatch(
        $testDiscussionArray,
        int $testLimit,
        int $testOffset,
        int $testTotalComments,
        ?string $testMaxDateInserted,
        $expected
    ) {
        $this->discussionModel->DateLastViewed = $testDiscussionArray['DateLastViewed'];
        $this->discussionModel->CountCommentWatch = $testDiscussionArray['CountCommentWatch'];
        $this->discussionModel->DateInserted = $testDiscussionArray['DateInserted'];
        $this->discussionModel->DateLastComment = $testDiscussionArray['DateLastComment'];
        $actual = $this->discussionModel->calculateWatch($this->discussionModel, $testLimit, $testOffset, $testTotalComments, $testMaxDateInserted);
        $this->assertSame($expected, $actual);
    }

    /**
     * Provide test data for {@link testCalculateWatch}.
     *
     * @return array Returns an array of test data.
     */
    public function provideTestCalculateWatchArrays() {
        $r = [
            'Unread Discussion With No Comments' => [
                [
                    'DateLastViewed' => null,
                    'CountCommentWatch' => null,
                    'DateInserted' => '2020-01-17 19:20:02',
                    'DateLastComment' => '2020-01-17 19:20:02',
                ],
                30,
                0,
                0,
                null,
                [0, '2020-01-17 19:20:02', 'insert'],
            ],
            'Unread Discussion with One Comment' => [
                [
                    'DateLastViewed' => null,
                    'CountCommentWatch' => null,
                    'DateInserted' => '2020-01-17 19:20:02',
                    'DateLastComment' => '2020-01-18 19:20:02',
                ],
                30,
                0,
                1,
                '2020-01-18 19:20:02',
                [1, '2020-01-18 19:20:02', 'insert'],
            ],
            'Unread Discussion with One More Total Comments than the Limit' => [
                [
                    'DateLastViewed' => null,
                    'CountCommentWatch' => null,
                    'DateInserted' => '2020-01-17 19:20:02',
                    'DateLastComment' => '2020-01-19 19:20:02',
                ],
                30,
                0,
                31,
                '2020-01-18 19:20:02',
                [30, '2020-01-18 19:20:02', 'insert'],
            ],
            'Read Discussion with No Comments' => [
                [
                    'DateLastViewed' => '2020-01-17 19:20:02',
                    'CountCommentWatch' => 0,
                    'DateInserted' => '2020-01-17 19:20:02',
                    'DateLastComment' => '2020-01-17 19:20:02',
                ],
                30,
                0,
                0,
                '2020-01-17 19:20:02',
                [0, '2020-01-17 19:20:02', null],
            ],
            'Read Discussion with New Comments' => [
                [
                    'DateLastViewed' => '2020-01-18 19:20:02',
                    'CountCommentWatch' => 5,
                    'DateInserted' => '2020-01-17 19:20:02',
                    'DateLastComment' => '2020-01-19 19:20:02',
                ],
                30,
                5,
                20,
                '2020-01-19 19:20:02',
                [20, '2020-01-19 19:20:02', 'update'],
            ],
            'User Has Read Page One, but not Page Two' => [
                [
                    'DateLastViewed' => '2020-01-18 19:20:02',
                    'CountCommentWatch' => 30,
                    'DateInserted' => '2020-01-17 19:20:02',
                    'DateLastComment' => '2020-01-19 19:20:02',
                ],
                30,
                30,
                31,
                '2020-01-19 19:20:02',
                [31, '2020-01-19 19:20:02', 'update'],
            ],
            'Comments Read is Greater than Total Comments' => [
                [
                    'DateLastViewed' => '2020-01-18 19:20:02',
                    'CountCommentWatch' => 6,
                    'DateInserted' => '2020-01-17 19:20:02',
                    'DateLastComment' => '2020-01-18 19:20:02',
                ],
                30,
                5,
                5,
                'DateLastComment' => '2020-01-18 19:20:02',
                [5, '2020-01-18 19:20:02', 'update'],
            ],
        ];

        return $r;
    }

    /**
     * Test {@link calculateCommentReadData()} against various scenarios.
     *
     * @param int $discussionCommentCount The number of comments in the discussion according to the Discussion Table.
     * @param string|null $discussionLastCommentDate Date of last Comment according to the Discussion table.
     * @param int|null $userReadComments Number of Comments the user has read according to the UserDiscussion table.
     * @param string|null $userLastReadDate Date of last Comment read according to the UserDiscussion table.
     * @param array $expected The expected result.
     * @dataProvider provideTestCalculateCommentReadData
     */
    public function testCalculateCommentReadData(
        int $discussionCommentCount,
        ?string $discussionLastCommentDate,
        ?int $userReadComments,
        ?string $userLastReadDate,
        $expected
    ) {
        $actual = $this->discussionModel->calculateCommentReadData(
            $discussionCommentCount,
            $discussionLastCommentDate,
            $userReadComments,
            $userLastReadDate
        );
        $this->assertSame($expected, $actual);
    }

    /**
     * Provide test data for testCalculateCommentReadData().
     *
     * @return array Returns an array of test data.
     */
    public function provideTestCalculateCommentReadData() {
        $r = [
            'discussionLastCommentDateIsNull' => [
                10,
                null,
                null,
                '2020-01-09 16:22:42',
                [true, 0],
            ],
            'userReadCommentIsNullWithReadDate' => [
                10,
                '2019-12-02 21:55:40',
                null,
                '2020-01-09 16:22:42',
                [true, 0],
            ],
            'userReadCommentIsNullWithUnreadDate' => [
                10,
                '2020-01-09 16:22:42',
                null,
                '2019-12-02 21:55:40',
                [false, 1],
            ],
            'userReadCommentsIsNullWithoutReadDate' => [
                10,
                '2019-12-02 21:55:40',
                null,
                null,
                [false, true],
            ],
            'CommentsAndUserReadEqualDatesConcur' => [
                10,
                '2019-12-02 21:55:40',
                10,
                '2020-01-09 16:22:42',
                [true, 0],
            ],
            'CommentsAndUserReadEqualDatesDisagree' => [
                10,
                '2020-01-09 16:22:42',
                10,
                '2019-12-02 21:55:40',
                [false, 1],
            ],
            'MoreCommentsThanReadDatesAgree' => [
                15,
                '2020-01-09 16:22:42',
                10,
                '2019-12-02 21:55:40',
                [false, 5],
            ],
            'MoreCommentsThanReadDatesDisagreeOnePage' => [
                15,
                '2019-12-02 21:55:40',
                10,
                '2020-01-09 16:22:42',
                [true, 0],
            ],
            'MoreReadThanCommentsLastCommentLater' => [
                5,
                '2020-01-09 16:22:42',
                10,
                '2019-12-02 21:55:40',
                [false, 1],
            ],
            'MoreReadThanCommentsLastCommentEarlier' => [
                5,
                '2019-12-02 21:55:40',
                10,
                '2020-01-09 16:22:42',
                [true, 0],
            ],
            'ReadCommentsNoDiscussionCommentsDiscussionNotRead' => [
                0,
                '2020-01-09 16:22:42',
                50,
                '2019-12-02 21:55:40',
                [false, true],
            ],
            'NullUserReadDateNoReadComments' => [
                5,
                '2020-01-09 16:22:42',
                0,
                null,
                [false, 5],
            ],
            'NullUserCountWithReadComments' => [
                5,
                '2020-01-09 16:22:42',
                10,
                null,
                [false, 1],
            ],
        ];

        return $r;
    }

    /**
     * Verify delete event dispatched during deletion.
     *
     * @return void
     */
    public function testDeleteEventDispatched(): void {
        $discussionID = $this->discussionModel->save([
            "Name" => __FUNCTION__,
            "Body" => "Hello world.",
            "Format" => "markdown",
        ]);
        $this->discussionModel->deleteID($discussionID);

        $this->assertInstanceOf(DiscussionEvent::class, $this->lastEvent);
        $this->assertEquals(DiscussionEvent::ACTION_DELETE, $this->lastEvent->getAction());
    }

    /**
     * Verify insert event dispatched during save.
     *
     * @return void
     */
    public function testSaveInsertEventDispatched(): void {
        $this->discussionModel->save([
            "Name" => __FUNCTION__,
            "Body" => "Hello world.",
            "Format" => "markdown",
        ]);
        $this->assertInstanceOf(DiscussionEvent::class, $this->lastEvent);
        $this->assertEquals(DiscussionEvent::ACTION_INSERT, $this->lastEvent->getAction());
    }

    /**
     * Verify update event dispatched during save.
     *
     * @return void
     */
    public function testSaveUpdateEventDispatched(): void {
        $discussionID = $this->discussionModel->save([
            "Name" => __FUNCTION__,
            "Body" => "Hello world.",
            "Format" => "markdown",
        ]);
        $this->discussionModel->save([
            "DiscussionID" => $discussionID,
            "Body" => "Hello again, world.",
        ]);

        $this->assertInstanceOf(DiscussionEvent::class, $this->lastEvent);
        $this->assertEquals(DiscussionEvent::ACTION_UPDATE, $this->lastEvent->getAction());
    }

    /**
     * Test inserting and updating a user's watch status of comments in a discussion.
     *
     * @return void
     * @throws \Exception Throws an exception if given an invalid timestamp.
     */
    public function testSetWatch(): void {
        $this->session->start(self::$siteInfo['adminUserID']);

        $countComments = 5;
        $discussion = [
            "CategoryID" => 1,
            "Name" => "Comment Watch Test",
            "Body" => "foo bar baz",
            "Format" => "Text",
            "CountComments" => $countComments,
            "InsertUserID" => 1,
        ];

        // Confirm the initial state, so changes are easy to detect.
        $discussionID = $this->discussionModel->save($discussion);
        $this->assertNotEmpty($discussionID, $this->discussionModel->Validation->resultsText());
        $discussion = $this->discussionModel->getID($discussionID);
        $this->assertIsObject($discussion);
        $this->assertNull(
            $discussion->CountCommentWatch,
            "Initial comment watch status not null."
        );

        // Create a comment watch status.
        $this->discussionModel->setWatch($discussion, 10, 0, $discussion->CountComments);
        $discussionFirstVisit = $this->discussionModel->getID($discussionID);
        $this->assertSame(
            $discussionFirstVisit->CountComments,
            $discussionFirstVisit->CountCommentWatch,
            "Creating new comment watch status failed."
        );

        // Update an existing comment watch status.
        $updatedCountComments = $countComments + 1;
        $this->discussionModel->setField($discussionID, "CountComments", $updatedCountComments);
        $this->discussionModel->setWatch($discussionFirstVisit, 10, 0, $updatedCountComments);
        $discussionSecondVisit = $this->discussionModel->getID($discussionID);
        $this->assertSame(
            $discussionSecondVisit->CountComments,
            $discussionSecondVisit->CountCommentWatch,
            "Updating comment watch status failed."
        );
    }

    /**
     * Test calculate() with various category marked read discussion dates.
     *
     * @param string $discussionInserted
     * @param string|null $discussionMarkedRead
     * @param string|null $categoryMarkedRead
     * @param string|null $expected
     * @dataProvider provideMarkedRead
     */
    public function testDiscussionCategoryMarkedRead(
        string $discussionInserted,
        ?string $discussionMarkedRead,
        ?string $categoryMarkedRead,
        ?string $expected
    ): void {
        // Set up a CategoryModel instance to test.
        CategoryModel::$Categories = [
            100 => [
                "Name" => "foo",
                "UrlCode" => "foo",
                "PermissionCategoryID" => 1,
                "DateMarkedRead" => $categoryMarkedRead,
            ]
        ];

        $discussion = (object)[
            "DiscussionID" => 0,
            "CategoryID" => 100,
            "Name" => "test",
            "Body" => "discuss",
            "InsertUserID" => 123,
            "DateInserted" => $discussionInserted,
            "Url" => "bar",
            "Attributes" => [],
            "Tags" => [],
            "LastCommentUserID" => 234,
            "CountComments" => 5,
            "DateLastComment" => "2020-01-01 16:22:42",
            "DateLastViewed" => $discussionMarkedRead,
            "CountCommentWatch" => $discussionMarkedRead ? 5 : null,
        ];

        $this->discussionModel->calculate($discussion);

        $this->assertSame($expected, $discussion->DateLastViewed);

        // Reset that static property.
        CategoryModel::$Categories = null;
    }

    /**
     * Provide data for testing date-marked-read calculations.
     *
     * @return array
     */
    public function provideMarkedRead(): array {
        $result = [
            "Discussion unread, category unread" => [
                "2020-01-01 00:00:00", // Discussion.DateInserted
                null, // Discussion.DateLastViewed
                null, // Category.DateMarkedRead
                null, // Expected value.
            ],
            "Discussion read, category unread." => [
                "2020-01-01 00:00:00",
                "2020-01-08 00:00:00",
                null,
                "2020-01-08 00:00:00",
            ],
            "Discussion read, category read more recently." => [
                "2020-01-01 00:00:00",
                "2020-01-08 00:00:00",
                "2020-01-10 00:00:00",
                "2020-01-10 00:00:00",
            ],
            "Discussion read, category read prior." => [
                "2020-01-01 00:00:00",
                "2020-01-22 00:00:00",
                "2020-01-08 00:00:00",
                "2020-01-22 00:00:00",
            ],
            "Discussion read, category read before discussion created." => [
                "2020-01-01 00:00:00",
                "2020-01-08 00:00:00",
                "2019-12-25 00:00:00",
                "2020-01-08 00:00:00",
            ],
            "Discussion unread, category read after discussion created." => [
                "2020-01-01 00:00:00",
                null,
                "2020-01-15 00:00:00",
                "2020-01-15 00:00:00",
            ],
            "Discussion unread, category read before discussion created." => [
                "2020-01-22 00:00:00",
                null,
                "2020-01-15 00:00:00",
                null,
            ],
        ];
        return $result;
    }

    /**
     * Announcements should properly sort.
     */
    public function testAnnouncementSorting() {
        $row = ['Name' => 'ax1', 'Announce' => 1];
        $this->insertDiscussions(10, $row);

        $rows = $this->discussionModel->getAnnouncements($row, 0, false, '-DiscussionID')->resultArray();
        TestSortingTrait::assertSorted($rows, '-DiscussionID');
    }
}
