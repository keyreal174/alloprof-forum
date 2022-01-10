<?php
/**
 * Hooks for Conversations.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Conversations
 * @since 2.0
 */

use Garden\Container\Container;
use Garden\Container\Reference;

/**
 * Handles hooks into Dashboard and Vanilla.
 */
class ConversationsHooks implements Gdn_IPlugin {
    /**
     * Handle the container init event to register things with the container.
     *
     * @param Container $dic
     */
    public function container_init(Container $dic) {
        $dic->rule(\Vanilla\Menu\CounterModel::class)
            ->addCall('addProvider', [new Reference(ConversationCounterProvider::class)])
        ;
    }

    /**
     *
     *
     * @param DbaController $sender
     */
    public function dbaController_countJobs_handler($sender) {
        $counts = [
            'Conversation' => ['CountMessages', 'CountParticipants', 'FirstMessageID', 'LastMessageID', 'DateUpdated', 'UpdateUserID']
        ];

        foreach ($counts as $table => $columns) {
            foreach ($columns as $column) {
                $name = "Recalculate $table.$column";
                $url = "/dba/counts.json?".http_build_query(['table' => $table, 'column' => $column]);

                $sender->Data['Jobs'][$name] = $url;
            }
        }
    }

    /**
     * Adds 'Discussions' tab to profiles and adds CSS & JS files to their head.
     *
     * @since 2.0.0
     * @package Vanilla
     *
     * @param ProfileController $sender
     */
    public function messagesController_addProfileTabsInfo_handler($sender) {
        if (is_object($sender->User) && $sender->User->UserID > 0) {
            $userID = Gdn::session()->UserID;
            // Add the discussion tab
            // $discussionsLabel = sprite('SpDiscussions').' '.t('Questions');
            // $commentsLabel = sprite('SpComments').' '.t('Comments');
            $discussionsLabel = "";
            $commentsLabel = "";
            if (c('Vanilla.Profile.ShowCounts', true)) {
                $discussionsCount = Gdn::sql()
                        ->select('d.DiscussionID', 'count', 'CountDiscussions')
                        ->from('Discussion d')
                        ->where(['d.InsertUserID' => Gdn::session()->UserID])
                        ->where(['d.Published' => 1])
                        ->get()
                        ->firstRow()
                        ->CountDiscussions;
                $commentsCount = Gdn::sql()
                        ->select('c.CommentID', 'count', 'CountComments')
                        ->from('Comment c')
                        ->join('Discussion d', 'd.DiscussionID = c.DiscussionID')
                        ->where(['d.InsertUserID' => Gdn::session()->UserID])
                        ->where(['c.Published' => 1])
                        ->get()
                        ->firstRow()
                        ->CountComments;
                $discussionsLabel .= $discussionsCount;
                $commentsLabel .= $commentsCount;
            }
            $sender->addProfileTab(t('questions asked'), userUrl($sender->User, '', 'discussions'), 'Questions', $discussionsLabel);
            $sender->addProfileTab(t('answers received'), userUrl($sender->User, '', 'comments'), 'Comments', $commentsLabel);
            // Add the discussion tab's CSS and Javascript.
            $sender->addJsFile('jquery.gardenmorepager.js');
            $sender->addJsFile('discussions.js', 'vanilla');
        }
    }

    /**
     * Remove data when deleting a user.
     *
     * @since 2.0.0
     * @access public
     */
    public function userModel_beforeDeleteUser_handler($sender) {
        $userID = val('UserID', $sender->EventArguments);
        $options = val('Options', $sender->EventArguments, []);
        $options = is_array($options) ? $options : [];

        $deleteMethod = val('DeleteMethod', $options, 'delete');
        if ($deleteMethod == 'delete') {
            /** @var Gdn_SQLDriver $sql */
            $sql = $sender->SQL;
            $sql
                ->from('UserConversation as uc')
                ->join('Conversation as c', 'c.ConversationID = uc.ConversationID')
                ->where(['c.InsertUserID' => $userID])
                ->orWhere(['c.UpdateUserID' => $userID])
                ->delete();
            $sql
                ->from('ConversationMessage as cm')
                ->join('Conversation as c', 'c.ConversationID = cm.ConversationID')
                ->where(['c.InsertUserID' => $userID])
                ->orWhere(['c.UpdateUserID' => $userID])
                ->delete();

            $sender->SQL->delete('Conversation', ['InsertUserID' => $userID]);
            $sender->SQL->delete('Conversation', ['UpdateUserID' => $userID]);
        } elseif ($deleteMethod == 'wipe') {
            $sender->SQL->update('ConversationMessage')
                ->set('Body', t('The user and all related content has been deleted.'))
                ->set('Format', 'Deleted')
                ->where('InsertUserID', $userID)
                ->put();
        }
        // Remove the user's profile information related to this application
        $sender->SQL->update('User')
            ->set('CountUnreadConversations', 0)
            ->where('UserID', $userID)
            ->put();
    }

    /**
     * Add 'Inbox' to profile menu.
     *
     * @since 2.0.0
     * @access public
     */
    public function profileController_addProfileTabs_handler($sender) {
        if (Gdn::session()->isValid()) {
            $inbox = t('Inbox');
            $inboxHtml = sprite('SpInbox').' '.$inbox;
            $inboxLink = '/messages/all';

            if (Gdn::session()->UserID != $sender->User->UserID) {
                // Accomodate admin access
                if (c('Conversations.Moderation.Allow', false) && Gdn::session()->checkPermission('Conversations.Moderation.Manage')) {
                    $countUnread = $sender->User->CountUnreadConversations;
                    $inboxLink .= "?userid={$sender->User->UserID}";
                } else {
                    return;
                }
            } else {
                // Current user
                $countUnread = Gdn::session()->User->CountUnreadConversations;
            }

            // if (is_numeric($countUnread) && $countUnread > 0) {
            //     $inboxHtml .= ' <span class="Aside"><span class="Count">'.$countUnread.'</span></span>';
            // }
            // $sender->addProfileTab($inbox, $inboxLink, 'Inbox', $inboxHtml);
        }
    }

    /**
     * Add "Message" option to profile options.
     */
    public function profileController_beforeProfileOptions_handler($sender, $args) {
        if (!$sender->EditMode &&
            Gdn::session()->UserID != $sender->User->UserID &&
            Gdn::session()->checkPermission('Conversations.Conversations.Add')
        ) {
            $sender->EventArguments['MemberOptions'][] = [
                'Text' => sprite('SpMessage').' '.t('Message'),
                'Url' => '/messages/add/'.rawurlencode($sender->User->Name),
                'CssClass' => 'MessageUser'
            ];
        }
    }


    /**
     * Additional options for the Preferences screen.
     *
     * @since 2.0.0
     * @access public
     */
    public function profileController_afterPreferencesDefined_handler($sender) {
        $sender->Preferences['Notifications']['Email.ConversationMessage'] = t('Notify me of private messages.');
        $sender->Preferences['Notifications']['Popup.ConversationMessage'] = t('Notify me of private messages.');
    }

    /**
     * Add 'Inbox' to global menu.
     *
     * @since 2.0.0
     * @access public
     */
    public function base_render_before($sender) {
        // $sender->addJsFile('component.js');
        $sender->addJsFile('alloprofObserver.js');
        $sender->addJsFile('setlocale.js', 'vanilla');
        $sender->addJsFile('slick.js', 'vanilla');
        $sender->addJsFile('slickcarousel.js', 'vanilla');
        $sender->addJsFile('jquery.popup.js');
        $sender->addJsFile('confirmfollow.js', 'vanilla');
        $sender->addJsFile('verify.js');
        $sender->addJsFile('select2.js', 'vanilla');
        $sender->addJsFile('basic.js', 'vanilla');
        $sender->addJsFile('notification.js', 'vanilla');
        $sender->addJsFile('askquestion.js', 'vanilla');

        // Add the menu options for conversations
        if ($sender->Menu && Gdn::session()->isValid()) {
            $inbox = t('Inbox');
            $countUnreadConversations = val('CountUnreadConversations', Gdn::session()->User);
            if (is_numeric($countUnreadConversations) && $countUnreadConversations > 0) {
                $inbox .= ' <span class="Alert">'.$countUnreadConversations.'</span>';
            }

            $sender->Menu->addLink('Conversations', $inbox, '/messages/all', false, ['Standard' => true]);
        }
    }

    /**
     * Let us add Messages to the Inbox page.
     */
    public function base_afterGetLocationData_handler($sender, $args) {
        $args['ControllerData']['Conversations/messages/inbox'] = t('Inbox Page');
    }

    /**
     * Provide default permissions for roles, based on the value in their Type column.
     *
     * @param PermissionModel $sender Instance of permission model that fired the event
     */
    public function permissionModel_defaultPermissions_handler($sender) {
        $sender->addDefault(
            RoleModel::TYPE_MEMBER,
            ['Conversations.Conversations.Add' => 1]
        );
        $sender->addDefault(
            RoleModel::TYPE_MODERATOR,
            ['Conversations.Conversations.Add' => 1]
        );
        $sender->addDefault(
            RoleModel::TYPE_ADMINISTRATOR,
            ['Conversations.Conversations.Add' => 1]
        );
    }

    /**
     * Database & config changes to be done upon enable.
     *
     * @since 2.0.0
     * @access public
     */
    public function setup() {
        $Database = Gdn::database();
        $Config = Gdn::factory(Gdn::AliasConfig);
        $Drop = false;
        $Validation = new Gdn_Validation(); // This is going to be needed by structure.php to validate permission names
        include(PATH_APPLICATIONS.DS.'conversations'.DS.'settings'.DS.'structure.php');
        include(PATH_APPLICATIONS.DS.'conversations'.DS.'settings'.DS.'stub.php');
    }
}
