<?php
/**
 * Quotes Plugin.
 *
 * @author Tim Gunter <tim@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package Quotes
 */

/**
 * This plugin allows users to quote comments for reference in their own comments
 * within a discussion.
 *
 * Changes:
 *  1.0     Initial release
 *  1.6.1   Overhaul
 *  1.6.4   Moved button to reactions area & changed js accordingly.
 *  1.6.8   Textarea target will now automatically resize to fit text body.
 *  1.6.9   Security fix.
 *  1.7     Eliminate livequery and js refactor.
 *  1.9     Use contentLoad to format quotes.
 */
class QuotesPlugin extends Gdn_Plugin {

    /** @var bool */
    public $HandleRenderQuotes = true;

    /**
     * Set some properties we always need.
     */
    public function __construct() {
        parent::__construct();

        if (function_exists('ValidateUsernameRegex')) {
            $this->ValidateUsernameRegex = ValidateUsernameRegex();
        } else {
            $this->ValidateUsernameRegex = "[\d\w_]{3,20}";
        }

        // Whether to handle drawing quotes or leave it up to some other plugin
        $this->HandleRenderQuotes = c('Plugins.Quotes.RenderQuotes', true);
    }

    /**
     * Add "Quote Settings" to edit profile menu.
     *
     * @param profileController $Sender
     */
    public function profileController_afterAddSideMenu_handler($Sender) {
        if (!Gdn::session()->checkPermission('Garden.SignIn.Allow')) {
            return;
        }

        $SideMenu = $Sender->EventArguments['SideMenu'];
        $ViewingUserID = Gdn::session()->UserID;

        if ($Sender->User->UserID == $ViewingUserID) {
            $SideMenu->addLink('Options', sprite('SpQuote').' '.t('Quote Settings'), '/profile/quotes', false, array('class' => 'Popup QuoteSettingsLink'));
        } else {
            $SideMenu->addLink('Options', sprite('SpQuote').' '.t('Quote Settings'), userUrl($Sender->User, '', 'quotes'), 'Garden.Users.Edit', array('class' => 'Popup QuoteSettingsLink'));
        }
    }

    /**
     * Endpoint for managing personal quote settings from edit profile menu.
     *
     * @param profileController $Sender
     */
    public function profileController_quotes_create($Sender) {
        $Sender->permission('Garden.SignIn.Allow');
        $Sender->title(t("Quote Settings"));

        $Args = $Sender->RequestArgs;
        if (sizeof($Args) < 2) {
            $Args = array_merge($Args, array(0, 0));
        } elseif (sizeof($Args) > 2) {
            $Args = array_slice($Args, 0, 2);
        }

        list($UserReference, $Username) = $Args;

        $Sender->getUserInfo($UserReference, $Username);
        $UserPrefs = dbdecode($Sender->User->Preferences);
        if (!is_array($UserPrefs)) {
            $UserPrefs = array();
        }

        $UserID = Gdn::session()->UserID;
        $ViewingUserID = $UserID;

        if ($Sender->User->UserID != $ViewingUserID) {
            $Sender->permission('Garden.Users.Edit');
            $UserID = $Sender->User->UserID;
            $Sender->setData('ForceEditing', $Sender->User->Name);
        } else {
            $Sender->setData('ForceEditing', false);
        }

        $QuoteFolding = val('Quotes.Folding', $UserPrefs, '1');
        $Sender->Form->setValue('QuoteFolding', $QuoteFolding);

        $Sender->setData('QuoteFoldingOptions', array(
            'None' => t("Don't fold quotes"),
            '1' => plural(1, '%s level deep', '%s levels deep'),
            '2' => plural(2, '%s level deep', '%s levels deep'),
            '3' => plural(3, '%s level deep', '%s levels deep'),
            '4' => plural(4, '%s level deep', '%s levels deep'),
            '5' => plural(5, '%s level deep', '%s levels deep')
        ));

        // Form submission handling.
        if ($Sender->Form->authenticatedPostBack()) {
            $NewFoldingLevel = $Sender->Form->getValue('QuoteFolding', '1');
            if ($NewFoldingLevel != $QuoteFolding) {
                Gdn::userModel()->savePreference($UserID, 'Quotes.Folding', $NewFoldingLevel);
                $Sender->informMessage(t("Your changes have been saved."));
            }
        }

        $Sender->render('quotes', '', 'plugins/Quotes');
    }

    /**
     * Set user's quote folding preference in the page for Javascript access.
     *
     * @param discussionController $Sender
     */
    public function discussionController_beforeDiscussionRender_handler($Sender) {
        if (!Gdn::session()->isValid()) {
            return;
        }

        $UserPrefs = dbdecode(Gdn::session()->User->Preferences);
        if (!is_array($UserPrefs)) {
            $UserPrefs = array();
        }

        $QuoteFolding = val('Quotes.Folding', $UserPrefs, '1');
        $Sender->addDefinition('QuotesFolding', $QuoteFolding);
        $Sender->addDefinition('&laquo; hide previous quotes', t('&laquo; hide previous quotes'));
        $Sender->addDefinition('&raquo; show previous quotes', t('&raquo; show previous quotes'));
    }

    /**
     * Re-dispatch for requests to our embedded controller.
     *
     * This is the old and busted way of doing controllers in addons. Use a native controller instead.
     *
     * @param $Sender
     * @throws Exception
     */
    public function pluginController_quotes_create($Sender) {
        $this->dispatch($Sender, $Sender->RequestArgs);
    }

    /**
     * Add getquote endpoint to our embedded controller.
     *
     * Old and busted method.
     *
     * @param $Sender
     */
    public function controller_getquote($Sender) {
        $this->discussionController_getQuote_create($Sender);
    }

    /**
     * Retrieve text of a quote.
     *
     * @param discussionController $Sender
     * @param $Selector
     * @param bool $Format
     */
    public function discussionController_getQuote_create($Sender, $Selector, $Format = false) {
        $Sender->deliveryMethod(DELIVERY_METHOD_JSON);
        $Sender->deliveryType(DELIVERY_TYPE_VIEW);

        if (!$Format) {
            $Format = c('Garden.InputFormatter');
        }

        $QuoteData = array(
            'status' => 'failed'
        );

        $QuoteData['selector'] = $Selector;
        list($Type, $ID) = explode('_', $Selector);
        $this->formatQuote($Type, $ID, $QuoteData, $Format);

        $Sender->setJson('Quote', $QuoteData);
        $Sender->render('GetQuote', '', 'plugins/Quotes');
    }

    /**
     * Add Javascript to discussion pages.
     *
     * @param discussionController $Sender
     */
    public function discussionController_render_before($Sender) {
        $Sender->addJsFile('quotes.js', 'plugins/Quotes');
    }

    /**
     * Add Javascript to post pages.
     *
     * @param postController $Sender
     */
    public function postController_render_before($Sender) {
        $Sender->addJsFile('quotes.js', 'plugins/Quotes');
    }

    /**
     * Add 'Quote' option to discussion via the reactions row after each post.
     *
     * @param Gdn_Controller $Sender
     * @param array $Args
     */
    public function base_AfterFlag_handler($Sender, $Args) {
        $this->addQuoteButton($Sender, $Args);
    }

    /**
     * Output Quote link.
     *
     * @param Gdn_Controller $Sender
     * @param array $Args
     */
    protected function addQuoteButton($Sender, $Args) {
        // There are some case were Discussion is not set as an event argument so we use the sender data instead.
        $discussion = $Sender->data('Discussion');
        if (!$discussion) {
            return;
        }

        if (!Gdn::session()->UserID) {
            return;
        }

        if (!Gdn::session()->checkPermission('Vanilla.Comments.Add', false, 'Category', $discussion->PermissionCategoryID)) {
            return;
        }

        if (isset($Args['Comment'])) {
            $Object = $Args['Comment'];
            $ObjectID = 'Comment_'.$Object->CommentID;
        } elseif ($discussion) {
            $Object = $discussion;
            $ObjectID = 'Discussion_'.$Object->DiscussionID;
        } else {
            return;
        }

        echo Gdn_Theme::BulletItem('Flags');
        echo anchor(sprite('ReactQuote', 'ReactSprite').' '.t('Quote'), url("post/quote/{$Object->DiscussionID}/{$ObjectID}", true), 'ReactButton Quote Visible').' ';
    }

    /**
     * Build quotes in a post.
     *
     * @param discussionController $Sender
     */
    public function discussionController_beforeDiscussionDisplay_handler($Sender) {
        $this->RenderQuotes($Sender);
    }

    public function postController_beforeDiscussionDisplay_handler($Sender) {
        $this->renderQuotes($Sender);
    }

    public function discussionController_beforeCommentDisplay_handler($Sender) {
        $this->renderQuotes($Sender);
    }

    public function postController_beforeCommentDisplay_handler($Sender) {
        $this->renderQuotes($Sender);
    }

    /**
     * Render quotes.
     *
     * @param $Sender
     */
    protected function renderQuotes($Sender) {
        if (!$this->HandleRenderQuotes) {
            return;
        }

        /** @var string|null $ValidateUsernameRegex */
        static $ValidateUsernameRegex = null;

        if (is_null($ValidateUsernameRegex)) {
            $ValidateUsernameRegex = sprintf("[%s]+", c('Garden.User.ValidationRegex', "\d\w_ "));
        }

        if (isset($Sender->EventArguments['Comment'])) {
            $Object = $Sender->EventArguments['Comment'];
        } elseif (isset($Sender->EventArguments['Discussion'])) {
            $Object = $Sender->EventArguments['Discussion'];
        } else {
            return;
        }

        switch ($Object->Format) {
            case 'Html':
                $Object->Body = preg_replace_callback("/(<blockquote\s+(?:class=\"(?:User)?Quote\")?\s+rel=\"([^\"]+)\">)/ui", array($this, 'QuoteAuthorCallback'), $Object->Body);
                $Object->Body = str_ireplace('</blockquote>', '</p></div></blockquote>', $Object->Body);
                break;
//         case 'Wysiwyg':
//            $Object->Body = preg_replace_callback("/(<blockquote\s+(?:class=\"(?:User)?Quote\")?\s+rel=\"([^\"]+)\">)/ui", array($this, 'QuoteAuthorCallback'), $Object->Body);
//            $Object->Body = str_ireplace('</blockquote>','</p></div></blockquote>',$Object->Body);
//            break;

            // WHY IS BBCODE PARSING DONE FOR MARKDOWN?
            case 'Markdown':
                // BBCode quotes with authors
                $Object->Body = preg_replace_callback("#(\[quote(\s+author)?=[\"']?(.*?)(\s+link.*?)?(;[\d]+)?[\"']?\])#usi", array($this, 'QuoteAuthorCallback'), $Object->Body);

                // BBCode quotes without authors
                $Object->Body = str_ireplace('[quote]', '<blockquote class="Quote UserQuote"><div class="QuoteText"><p>', $Object->Body);

                // End of BBCode quotes
                $Object->Body = str_ireplace('[/quote]', '</p></div></blockquote>', $Object->Body);
                break;

            case 'Display':
            case 'Text':
            case 'TextEx':
            default:
                break;
        }
    }

    /**
     * Get HTML reference to the quote author.
     *
     * @param array $Matches
     * @return string HTML.
     */
    protected function quoteAuthorCallback($Matches) {
        $Attribution = t('%s said:');
        $Link = anchor($Matches[2], '/profile/'.$Matches[2], '', array('rel' => 'nofollow'));
        $Attribution = sprintf($Attribution, $Link);
        return <<<BLOCKQUOTE
      <blockquote class="UserQuote"><div class="QuoteAuthor">{$Attribution}</div><div class="QuoteText"><p>
BLOCKQUOTE;
    }

    /**
     * Quote endpoint.
     *
     * @param postController $Sender
     */
    public function postController_quote_create($Sender) {
        if (sizeof($Sender->RequestArgs) < 2) {
            return;
        }
        $Selector = $Sender->RequestArgs[1];
        $Sender->setData('Plugin.Quotes.QuoteSource', $Selector);
        $Sender->View = 'comment';
        return $Sender->comment();
    }

    /**
     * Format quotes on the posting page.
     *
     * @param postController $Sender
     */
    public function postController_BeforeCommentRender_handler($Sender) {
        if ($Sender->data('Plugin.Quotes.QuoteSource')) {
            if (sizeof($Sender->RequestArgs) < 2) {
                return;
            }
            $Selector = $Sender->RequestArgs[1];
            list($Type, $ID) = explode('_', $Selector);
            $QuoteData = array(
                'status' => 'failed'
            );
            $this->formatQuote($Type, $ID, $QuoteData);
            if ($QuoteData['status'] == 'success') {
                $Sender->Form->setValue('Body', "{$QuoteData['body']}\n");
            }
        }
    }

    /**
     * Format the quote.
     *
     * @param string $Type
     * @param int $ID
     * @param array $QuoteData
     * @param bool $Format
     */
    protected function formatQuote($Type, $ID, &$QuoteData, $Format = false) {
        // Temporarily disable Emoji parsing (prevent double-parsing to HTML)
        $emojiEnabled = Emoji::instance()->enabled;
        Emoji::instance()->enabled = false;

        if (!$Format) {
            $Format = c('Garden.InputFormatter');
        }

        $Type = strtolower($Type);
        $Model = false;
        switch ($Type) {
            case 'comment':
                $Model = new CommentModel();
                break;

            case 'discussion':
                $Model = new DiscussionModel();
                break;

            default:
                break;
        }

        //$QuoteData = array();
        if ($Model) {
            $Data = $Model->getID($ID);
            $NewFormat = $Format;
            if ($NewFormat == 'Wysiwyg') {
                $NewFormat = 'Html';
            }
            $QuoteFormat = $Data->Format;
            if ($QuoteFormat == 'Wysiwyg') {
                $QuoteFormat = 'Html';
            }

            // Perform transcoding if possible
            $NewBody = $Data->Body;
            if ($QuoteFormat != $NewFormat) {
                if (in_array($NewFormat, array('Html', 'Wysiwyg'))) {
                    $NewBody = Gdn_Format::to($NewBody, $QuoteFormat);
                } elseif ($QuoteFormat == 'Html' && $NewFormat == 'BBCode') {
                    $NewBody = Gdn_Format::text($NewBody, false);
                } elseif ($QuoteFormat == 'Text' && $NewFormat == 'BBCode') {
                    $NewBody = Gdn_Format::text($NewBody, false);
                } else {
                    $NewBody = Gdn_Format::plainText($NewBody, $QuoteFormat);
                }

                if (!in_array($NewFormat, array('Html', 'Wysiwyg'))) {
                    Gdn::controller()->informMessage(sprintf(
                        t('The quote had to be converted from %s to %s.', 'The quote had to be converted from %s to %s. Some formatting may have been lost.'),
                        htmlspecialchars($QuoteFormat),
                        htmlspecialchars($NewFormat)
                    ));
                }
            }
            $Data->Body = $NewBody;
            $this->EventArguments['String'] = &$Data->Body;
            $this->fireEvent('FilterContent');

            // Format the quote according to the format.
            switch ($Format) {
                case 'Html':   // HTML
                    $Quote = '<blockquote class="Quote" rel="'.htmlspecialchars($Data->InsertName).'">'.$Data->Body.'</blockquote>'."\n";
                    break;

                case 'BBCode':
                    $Author = htmlspecialchars($Data->InsertName);
                    if ($ID) {
                        $IDString = ';'.($Type === 'comment' ? 'c' : 'd').'-'.htmlspecialchars($ID);
                    }

                    $QuoteBody = $Data->Body;

                    // TODO: Strip inner quotes...
//                  $QuoteBody = trim(preg_replace('`(\[quote.*/quote\])`si', '', $QuoteBody));

                    $Quote = <<<BQ
[quote="{$Author}{$IDString}"]{$QuoteBody}[/quote]

BQ;
                    break;

                case 'Markdown':
                case 'Display':
                case 'Text':
                case 'TextEx':
                    $QuoteBody = $Data->Body;
                    $insertName = $Data->InsertName;
                    if (strpos($insertName, ' ') !== false) {
                        $insertName = '"'.$insertName.'"';
                    }
                    $Quote = '> '.sprintf(t('%s said:'), '@'.$insertName)."\n".
                        '> '.str_replace("\n", "\n> ", $QuoteBody)."\n";

                    break;
                case 'Wysiwyg':
                    $Attribution = sprintf(t('%s said:'), userAnchor($Data, null, array('Px' => 'Insert')));
                    $QuoteBody = $Data->Body;

                    // TODO: Strip inner quotes...
//                  $QuoteBody = trim(preg_replace('`(<blockquote.*/blockquote>)`si', '', $QuoteBody));

                    $Quote = <<<BLOCKQUOTE
<blockquote class="Quote">
  <div class="QuoteAuthor">$Attribution</div>
  <div class="QuoteText">$QuoteBody</div>
</blockquote>

BLOCKQUOTE;

                    break;
            }

            $QuoteData = array_merge($QuoteData, array(
                'status' => 'success',
                'body' => $Quote,
                'format' => $Format,
                'authorid' => $Data->InsertUserID,
                'authorname' => $Data->InsertName,
                'type' => $Type,
                'typeid' => $ID
            ));
        }

        // Undo Emoji disable.
        Emoji::instance()->enabled = $emojiEnabled;
    }

    /**
     * Extra parsing for Markdown.
     *
     * @param string $Text
     * @return string
     */
    protected static function _stripMarkdownQuotes($Text) {
        $Text = preg_replace('/
              (                                # Wrap whole match in $1
                (?>
                  ^[ ]*>[ ]?            # ">" at the start of a line
                    .+\n                    # rest of the first line
                  (.+\n)*                    # subsequent consecutive lines
                  \n*                        # blanks
                )+
              )
            /xm', '', $Text);

        return $Text;
    }

    /**
     * Remove mentions from quotes so we don't generate notifications.
     *
     * @param string $Text
     * @return string
     */
    protected static function _stripMentions($Text) {
        $Text = preg_replace(
            '/(^|[\s,\.>])@(\w{1,50})\b/i',
            '$1$2',
            $Text
        );

        return $Text;
    }
}
