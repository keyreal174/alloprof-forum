<?php
/**
 * Messages controller.
 *
 * @copyright 2009-2015 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package Conversations
 * @since 2.0
 */

/**
 * MessagesController handles displaying lists of conversations and conversation messages.
 */
class MessagesController extends ConversationsController {

    /** @var array Models to include. */
    public $Uses = array('Form', 'ConversationModel', 'ConversationMessageModel');

    /**  @var ConversationModel */
    public $ConversationModel;

    /** @var object A dataset of users taking part in this discussion. Used by $this->Index. */
    public $RecipientData;

    /** @var int The current offset of the paged data set. Defined and used by $this->Index and $this->All. */
    public $Offset;

    /**
     * Highlight route and include JS, CSS, and modules used by all methods.
     *
     * Always called by dispatcher before controller's requested method.
     *
     * @since 2.0.0
     * @access public
     */
    public function Initialize() {
        parent::Initialize();
        $this->Menu->HighlightRoute('/messages/inbox');
        $this->SetData('Breadcrumbs', array(array('Name' => T('Inbox'), 'Url' => '/messages/inbox')));
//      $this->AddModule('MeModule');
        $this->AddModule('SignedInModule');

        if (CheckPermission('Conversations.Conversations.Add')) {
            $this->AddModule('NewConversationModule');
        }
    }

    /**
     * Start a new conversation.
     *
     * @since 2.0.0
     * @access public
     *
     * @param string $Recipient Username of the recipient.
     */
    public function Add($Recipient = '') {
        $this->Permission('Conversations.Conversations.Add');
        $this->Form->SetModel($this->ConversationModel);

        // Set recipient limit
        if (!CheckPermission('Garden.Moderation.Manage') && C('Conversations.MaxRecipients')) {
            $this->AddDefinition('MaxRecipients', C('Conversations.MaxRecipients'));
            $this->SetData('MaxRecipients', C('Conversations.MaxRecipients'));
        }

        if ($this->Form->AuthenticatedPostBack()) {
            $RecipientUserIDs = array();
            $To = explode(',', $this->Form->GetFormValue('To', ''));
            $UserModel = new UserModel();
            foreach ($To as $Name) {
                if (trim($Name) != '') {
                    $User = $UserModel->GetByUsername(trim($Name));
                    if (is_object($User)) {
                        $RecipientUserIDs[] = $User->UserID;
                    }
                }
            }

            // Enforce MaxRecipients
            if (!$this->ConversationModel->AddUserAllowed(0, count($RecipientUserIDs))) {
                // Reuse the Info message now as an error.
                $this->Form->AddError(sprintf(
                    Plural(
                        $this->Data('MaxRecipients'),
                        "You are limited to %s recipient.",
                        "You are limited to %s recipients."
                    ),
                    C('Conversations.MaxRecipients')
                ));
            }

            $this->EventArguments['Recipients'] = $RecipientUserIDs;
            $this->FireEvent('BeforeAddConversation');

            $this->Form->SetFormValue('RecipientUserID', $RecipientUserIDs);
            $ConversationID = $this->Form->Save($this->ConversationMessageModel);
            if ($ConversationID !== false) {
                $Target = $this->Form->GetFormValue('Target', 'messages/'.$ConversationID);
                $this->RedirectUrl = Url($Target);

                $Conversation = $this->ConversationModel->GetID($ConversationID, Gdn::Session()->UserID);
                $NewMessageID = val('FirstMessageID', $Conversation);
                $this->EventArguments['MessageID'] = $NewMessageID;
                $this->FireEvent('AfterConversationSave');
            }
        } else {
            if ($Recipient != '') {
                $this->Form->SetValue('To', $Recipient);
            }
        }
        if ($Target = Gdn::Request()->Get('Target')) {
            $this->Form->AddHidden('Target', $Target);
        }

        Gdn_Theme::Section('PostConversation');
        $this->Title(T('New Conversation'));
        $this->SetData('Breadcrumbs', array(array('Name' => T('Inbox'), 'Url' => '/messages/inbox'), array('Name' => $this->Data('Title'), 'Url' => 'messages/add')));
        $this->Render();
    }

    /**
     * Add a message to a conversation.
     *
     * @since 2.0.0
     * @access public
     *
     * @param int $ConversationID Unique ID of the conversation.
     */
    public function AddMessage($ConversationID = '') {
        $this->Form->SetModel($this->ConversationMessageModel);
        if (is_numeric($ConversationID) && $ConversationID > 0) {
            $this->Form->AddHidden('ConversationID', $ConversationID);
        }

        if ($this->Form->AuthenticatedPostBack()) {
            $ConversationID = $this->Form->GetFormValue('ConversationID', '');

            // Make sure the user posting to the conversation is actually
            // a member of it, or is allowed, like an admin.
            if (!CheckPermission('Garden.Moderation.Manage')) {
                $UserID = Gdn::Session()->UserID;
                $ValidConversationMember = $this->ConversationModel->ValidConversationMember($ConversationID, $UserID);
                if (!$ValidConversationMember) {
                    throw PermissionException();
                }
            }

            $Conversation = $this->ConversationModel->GetID($ConversationID, Gdn::Session()->UserID);

            $this->EventArguments['Conversation'] = $Conversation;
            $this->EventArguments['ConversationID'] = $ConversationID;
            $this->FireEvent('BeforeAddMessage');

            $NewMessageID = $this->Form->Save();

            if ($NewMessageID) {
                if ($this->DeliveryType() == DELIVERY_TYPE_ALL) {
                    Redirect('messages/'.$ConversationID.'/#'.$NewMessageID, 302);
                }

                $this->SetJson('MessageID', $NewMessageID);

                $this->EventArguments['MessageID'] = $NewMessageID;
                $this->FireEvent('AfterMessageSave');

                // If this was not a full-page delivery type, return the partial response
                // Load all new messages that the user hasn't seen yet (including theirs)
                $LastMessageID = $this->Form->GetFormValue('LastMessageID');
                if (!is_numeric($LastMessageID)) {
                    $LastMessageID = $NewMessageID - 1;
                }

                $Session = Gdn::Session();
                $MessageData = $this->ConversationMessageModel->GetNew($ConversationID, $LastMessageID);
                $this->Conversation = $Conversation;
                $this->MessageData = $MessageData;
                $this->SetData('Messages', $MessageData);

                $this->View = 'messages';
            } else {
                // Handle ajax based errors...
                if ($this->DeliveryType() != DELIVERY_TYPE_ALL) {
                    $this->ErrorMessage($this->Form->Errors());
                }
            }
        }
        $this->Render();
    }

    /**
     * Show all conversations for the currently authenticated user.
     *
     * @since 2.0.0
     * @access public
     *
     * @param string $Page
     */
    public function All($Page = '') {
        $Session = Gdn::Session();
        $this->Title(T('Inbox'));
        Gdn_Theme::Section('ConversationList');

        list($Offset, $Limit) = OffsetLimit($Page, C('Conversations.Conversations.PerPage', 50));

        // Calculate offset
        $this->Offset = $Offset;

        $UserID = $this->Request->Get('userid', Gdn::Session()->UserID);
        if ($UserID != Gdn::Session()->UserID) {
            if (!C('Conversations.Moderation.Allow', false)) {
                throw PermissionException();
            }
            $this->Permission('Conversations.Moderation.Manage');
        }

        $Conversations = $this->ConversationModel->Get2($UserID, $Offset, $Limit);
        $this->SetData('Conversations', $Conversations->ResultArray());

        // Get Conversations Count
        //$CountConversations = $this->ConversationModel->GetCount($UserID);
        //$this->SetData('CountConversations', $CountConversations);

        // Build the pager
        if (!$this->Data('_PagerUrl')) {
            $this->SetData('_PagerUrl', 'messages/all/{Page}');
        }
        $this->SetData('_Page', $Page);
        $this->SetData('_Limit', $Limit);
        $this->SetData('_CurrentRecords', count($Conversations->ResultArray()));

        // Deliver json data if necessary
        if ($this->_DeliveryType != DELIVERY_TYPE_ALL && $this->_DeliveryMethod == DELIVERY_METHOD_XHTML) {
            $this->SetJson('LessRow', $this->Pager->ToString('less'));
            $this->SetJson('MoreRow', $this->Pager->ToString('more'));
            $this->View = 'conversations';
        }

        // Build and display page.
        $this->Render();
    }

    /**
     * Clear the message history for a specific conversation & user.
     *
     * @since 2.0.0
     * @access public
     *
     * @param int $ConversationID Unique ID of conversation to clear.
     */
    public function Clear($ConversationID = false, $TransientKey = '') {
        $Session = Gdn::Session();

        // Yes/No response
        $this->_DeliveryType = DELIVERY_TYPE_BOOL;

        $ValidID = (is_numeric($ConversationID) && $ConversationID > 0);
        $ValidSession = ($Session->UserID > 0 && $Session->ValidateTransientKey($TransientKey));

        if ($ValidID && $ValidSession) {
            // Clear it
            $this->ConversationModel->Clear($ConversationID, $Session->UserID);
            $this->InformMessage(T('The conversation has been cleared.'));
            $this->RedirectUrl = Url('/messages/all');
        }

        $this->Render();
    }

    /**
     * Shows all uncleared messages within a conversation for the viewing user
     *
     * @since 2.0.0
     * @access public
     *
     * @param int $ConversationID Unique ID of conversation to view.
     * @param int $Offset Number to skip.
     * @param int $Limit Number to show.
     */
    public function Index($ConversationID = false, $Offset = -1, $Limit = '') {
        $this->Offset = $Offset;
        $Session = Gdn::Session();
        Gdn_Theme::Section('Conversation');

        // Figure out Conversation ID
        if (!is_numeric($ConversationID) || $ConversationID < 0) {
            $ConversationID = 0;
        }

        // Form setup for adding comments
        $this->Form->SetModel($this->ConversationMessageModel);
        $this->Form->AddHidden('ConversationID', $ConversationID);

        // Check permissions on the recipients.
        $InConversation = $this->ConversationModel->InConversation($ConversationID, Gdn::Session()->UserID);

        if (!$InConversation) {
            // Conversation moderation must be enabled and they must have permission
            if (!C('Conversations.Moderation.Allow', false)) {
                throw PermissionException();
            }
            $this->Permission('Conversations.Moderation.Manage');
        }

        $this->Conversation = $this->ConversationModel->GetID($ConversationID);
        $this->Conversation->Participants = $this->ConversationModel->GetRecipients($ConversationID);
        $this->SetData('Conversation', $this->Conversation);

        // Bad conversation? Redirect
        if ($this->Conversation === false) {
            throw NotFoundException('Conversation');
        }

        // Get limit
        if ($Limit == '' || !is_numeric($Limit) || $Limit < 0) {
            $Limit = Gdn::Config('Conversations.Messages.PerPage', 50);
        }

        // Calculate counts
        if (!is_numeric($this->Offset) || $this->Offset < 0) {
            // Round down to the appropriate offset based on the user's read messages & messages per page
            $CountReadMessages = $this->Conversation->CountMessages - $this->Conversation->CountNewMessages;
            if ($CountReadMessages < 0) {
                $CountReadMessages = 0;
            }

            if ($CountReadMessages > $this->Conversation->CountMessages) {
                $CountReadMessages = $this->Conversation->CountMessages;
            }

            // (((67 comments / 10 perpage) = 6.7) rounded down = 6) * 10 perpage = offset 60;
            $this->Offset = floor($CountReadMessages / $Limit) * $Limit;

            // Send the hash link in.
            if ($CountReadMessages > 1) {
                $this->AddDefinition('LocationHash', '#Item_'.$CountReadMessages);
            }
        }

        // Fetch message data
        $this->MessageData = $this->ConversationMessageModel->Get(
            $ConversationID,
            $Session->UserID,
            $this->Offset,
            $Limit
        );

        $this->SetData('Messages', $this->MessageData);

        // Figure out who's participating.
        $ParticipantTitle = ConversationModel::ParticipantTitle($this->Conversation, true);
        $this->Participants = $ParticipantTitle;

        $this->Title(strip_tags($this->Participants));

        // $CountMessages = $this->ConversationMessageModel->GetCount($ConversationID, $Session->UserID);

        // Build a pager
        $PagerFactory = new Gdn_PagerFactory();
        $this->Pager = $PagerFactory->GetPager('MorePager', $this);
        $this->Pager->MoreCode = 'Newer Messages';
        $this->Pager->LessCode = 'Older Messages';
        $this->Pager->ClientID = 'Pager';
        $this->Pager->Configure(
            $this->Offset,
            $Limit,
            $this->Conversation->CountMessages,
            'messages/'.$ConversationID.'/%1$s/%2$s/'
        );

        // Mark the conversation as ready by this user.
        $this->ConversationModel->MarkRead($ConversationID, $Session->UserID);

        // Deliver json data if necessary
        if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
            $this->SetJson('LessRow', $this->Pager->ToString('less'));
            $this->SetJson('MoreRow', $this->Pager->ToString('more'));
            $this->View = 'messages';
        }

        // Add modules.
        $ClearHistoryModule = new ClearHistoryModule($this);
        $ClearHistoryModule->ConversationID($ConversationID);
        $this->AddModule($ClearHistoryModule);

        $InThisConversationModule = new InThisConversationModule($this);
        $InThisConversationModule->SetData($this->Conversation->Participants);
        $this->AddModule($InThisConversationModule);

        // Doesn't make sense for people who can't even start conversations to be adding people
        if (CheckPermission('Conversations.Conversations.Add')) {
            $this->AddModule('AddPeopleModule');
        }

        $Subject = $this->Data('Conversation.Subject');
        if (!$Subject) {
            $Subject = T('Message');
        }

        $this->Data['Breadcrumbs'][] = array(
            'Name' => $Subject,
            'Url' => Url('', '//'));

        // Render view
        $this->Render();
    }

    public function GetNew($ConversationID, $LastMessageID = null) {
        $this->RecipientData = $this->ConversationModel->GetRecipients($ConversationID);
        $this->SetData('Recipients', $this->RecipientData);

        // Check permissions on the recipients.
        $InConversation = false;
        foreach ($this->RecipientData->Result() as $Recipient) {
            if ($Recipient->UserID == Gdn::Session()->UserID) {
                $InConversation = true;
                break;
            }
        }

        if (!$InConversation) {
            // Conversation moderation must be enabled and they must have permission
            if (!C('Conversations.Moderation.Allow', false)) {
                throw PermissionException();
            }
            $this->Permission('Conversations.Moderation.Manage');
        }

        $this->Conversation = $this->ConversationModel->GetID($ConversationID);
        $this->SetData('Conversation', $this->Conversation);

        // Bad conversation? Redirect
        if ($this->Conversation === false) {
            throw NotFoundException('Conversation');
        }

        $Where = array();
        if ($LastMessageID) {
            if (strpos($LastMessageID, '_') !== false) {
                $LastMessageID = array_pop(explode('_', $LastMessageID));
            }

            $Where['MessageID >='] = $LastMessageID;
        }

        // Fetch message data
        $this->SetData(
            'MessageData',
            $this->ConversationMessageModel->Get(
                $ConversationID,
                Gdn::Session()->UserID,
                0,
                50,
                $Where
            ),
            true
        );

        $this->Render('Messages');
    }

    public function Popin() {
        $this->Permission('Garden.SignIn.Allow');

        // Fetch from model
        $Conversations = $this->ConversationModel->Get2(
            Gdn::Session()->UserID,
            0,
            5
        )->ResultArray();

        // Last message user data
        Gdn::UserModel()->JoinUsers($Conversations, array('LastInsertUserID'));

        // Join in the participants.
        $this->SetData('Conversations', $Conversations);
        $this->Render();
    }

    /**
     * Allows users to bookmark conversations.
     *
     * @since 2.0.0
     * @access public
     *
     * @param int $ConversationID Unique ID of conversation to view.
     * @param string $TransientKey Single-use hash to prove intent.
     */
    public function Bookmark($ConversationID = '', $TransientKey = '') {
        $Session = Gdn::Session();
        $Success = false;
        $Star = false;

        // Validate & do bookmarking
        if (is_numeric($ConversationID)
            && $ConversationID > 0
            && $Session->UserID > 0
            && $Session->ValidateTransientKey($TransientKey)
        ) {
            $Bookmark = $this->ConversationModel->Bookmark($ConversationID, $Session->UserID);
        }

        // Report success or error
        if ($Bookmark === false) {
            $this->Form->AddError('ErrorBool');
        } else {
            $this->SetJson('Bookmark', $Bookmark);
        }

        // Redirect back where the user came from if necessary
        if ($this->_DeliveryType == DELIVERY_TYPE_ALL) {
            Redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->Render();
        }
    }

    /**
     * Show bookmarked conversations for the current user.
     *
     * @since 2.0.0
     * @access public
     *
     * @param int $Offset Number to skip.
     * @param string $Limit Number to show.
     */
//   public function Bookmarked($Offset = 0, $Limit = '') {
//      $this->View = 'All';
//      $this->All($Offset, $Limit, TRUE);
//   }

    /**
     * Show bookmarked conversations for the current user.
     *
     * @since 2.0.0
     * @access public
     *
     * @param int $Offset Number to skip.
     * @param string $Limit Number to show.
     */
    public function Inbox($Page = '') {
        $this->View = 'All';
        $this->All($Page);
    }
}
