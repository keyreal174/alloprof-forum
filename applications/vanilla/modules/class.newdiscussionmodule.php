<?php
/**
 * New Discussion module
 *
 * @copyright 2009-2020 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Vanilla
 * @since 2.0
 */

use Vanilla\Contracts\LocaleInterface;

/**
 * Renders the "Start a New Discussion" button.
 */
class NewDiscussionModule extends Gdn_Module {

    use \Vanilla\Formatting\FormatCompatTrait;

    /** @var LocaleInterface */
    private $locale;

    /** @var int Which category we are viewing (if any). */
    public $CategoryID = null;

    /** @var string Which button will be used as the default. */
    public $DefaultButton;

    /** @var string CSS classes to apply to ButtonGroup. */
    public $CssClass = 'Button Action Big Primary';

    /** @var string Query string to append to button URL. */
    public $QueryString = '';

    /** @var array An associative array of form types and their locations. */
    public $FormCollection;

    /** @var array Collection of buttons to display. */
    public $Buttons = [];

    /** @var bool Whether to show button to all users & guests regardless of permissions. */
    public $ShowGuests = false;

    /** @var string Where to send users without permission when $SkipPermissions is enabled. */
    public $GuestUrl = '/entry/signin';

    /** @var boolean Reorder HTML for easier styling */
    public $reorder = false;

    public $ShowCategorySelector = false;

    public $Form;
    /**
     * Set default button.
     *
     * @param string $sender
     * @param bool $applicationFolder Unused.
     */
    public function __construct($sender = '', $applicationFolder = false) {
        parent::__construct($sender, 'Vanilla');
        $this->locale = Gdn::getContainer()->get(\Vanilla\Contracts\LocaleInterface::class);
        // Customize main button by setting Vanilla.DefaultNewButton to URL code. Example: "post/question"
        $this->DefaultButton = c('Vanilla.DefaultNewButton', false);
        $this->ShowCategorySelector = (bool)c('Vanilla.Categories.Use');
    }


    /**
     *
     *
     * @return string
     */
    public function assetTarget() {
        return 'Content';
    }

    /**
     * Add a button to the collection.
     *
     * @param string $text
     * @param string $url
     * @param bool $asOwnButton Whether to display as a separate button or not.
     */
    public function addButton($text, $url, $asOwnButton = false) {
        $this->Buttons[] = ['Text' => $text, 'Url' => $url, 'asOwnButton' => $asOwnButton];
    }

    /**
     * Render the module.
     *
     * @return string
     */
    public function toString() {

        // Define the form for the comment input
        $this->Form = Gdn::factory('Form', 'Discussion');
        $this->Form->Action = url('/post/discussion');

        if (Gdn::config('Garden.ForceInputFormatter')) {
            $format = Gdn::config('Garden.InputFormatter', '');
            $this->Form->setFormValue('Format', $format);
        }

        $inputFormatter = $this->Form->getFormValue('Format', c('Garden.InputFormatter'));

        $this->Form->setFormValue(
            'Body',
            nl2br($this->Form->getFormValue('Body'))
        );

        // Set CategoryID if we have one.
        if (c('Vanilla.Categories.Use', true) && $this->CategoryID === null) {
            $this->CategoryID = Gdn::controller()->data('Category.CategoryID', Gdn::controller()->data('ContextualCategoryID', false));
        }

        // Allow plugins and themes to modify parameters.
        Gdn::controller()->EventArguments['NewDiscussionModule'] = &$this;
        Gdn::controller()->fireEvent('BeforeNewDiscussionButton');

        // Make sure the user has the most basic of permissions first.
        $permissionCategory = CategoryModel::permissionCategory($this->CategoryID);
        if ($this->CategoryID) {
            $category = CategoryModel::categories($this->CategoryID);
            $hasPermission = CategoryModel::checkPermission($this->CategoryID, 'Vanilla.Discussions.Add');
        } else {
            $hasPermission = Gdn::session()->checkPermission('Vanilla.Discussions.Add', true, 'Category', 'any');
        }

        // Determine if this is a guest & we're using "New Discussion" button as call to action.
        $privilegedGuest = ($this->ShowGuests && !Gdn::session()->isValid());

        // No module for you!
        if (!$hasPermission && !$privilegedGuest) {
            $this->invalid = true;
        }

        // Grab the allowed discussion types.
        $discussionTypes = CategoryModel::allowedDiscussionTypes($permissionCategory, isset($category) ? $category : [], $this->_Sender);
        $buttonsConfig = c('NewDiscussionModule.Types', []);

        foreach ($discussionTypes as $key => $type) {
            if (isset($type['AddPermission']) && !Gdn::session()->checkPermission($type['AddPermission'])) {
                unset($discussionTypes[$key]);
                continue;
            }

            $url = val('AddUrl', $type);
            if (!$url) {
                continue;
            }

            $hasQuery = strpos($url, '?') !== false;

            if (!$hasQuery) {
                if (isset($category)) {
                    $url .= '/'.rawurlencode(val('UrlCode', $category));
                }

                // Present a signin redirect for a $PrivilegedGuest.
                if (!$hasPermission) {
                    $url = $this->GuestUrl.'?Target='.$url;
                }
            }
            // Check whether to display in dropdown or as a separate button.
            $asOwnButton = $buttonsConfig[$type['Singular']]['AsOwnButton'] ?? false;

            $this->addButton(
                $this->locale->translate($type['AddText'] ?? ''),
                $url,
                $asOwnButton
            );
        }

        // Add QueryString to URL if one is defined.
        if ($this->QueryString && $hasPermission) {
            foreach ($this->Buttons as &$row) {
                $row['Url'] .= (strpos($row['Url'], '?') !== false ? '&' : '?').$this->QueryString;
            }
        }

        return parent::toString();
    }

    /**
     * Groups buttons according to whether they are standalone or part of a dropdown.
     *
     * @return array Returns buttons grouped by whether they are standalone or part of a dropdown.
     */
    public function getButtonGroups(): array {
        $allButtons = [];
        $groupedButtons = [];
        foreach ($this->Buttons as $key => $button) {
            if ($button['asOwnButton']) {
                $allButtons[] = [$button];
            } else {
                $groupedButtons[] = $button;
            }
        }
        if (!empty($groupedButtons)) {
            array_unshift($allButtons, $groupedButtons);
        }
        return $allButtons;
    }
}
