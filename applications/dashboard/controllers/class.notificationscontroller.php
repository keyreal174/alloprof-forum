<?php
/**
 * Creates and sends notifications to user.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Dashboard
 * @since 2.0
 */

use Vanilla\Formatting\Formats\HtmlFormat;

/**
 * Handle /notifications endpoint.
 */
class NotificationsController extends Gdn_Controller {

    /**
     * CSS, JS and module includes.
     */
    public function initialize() {
        $this->Head = new HeadModule($this);
        $this->addJsFile('jquery.js');
        $this->addJsFile('jquery.form.js');
        $this->addJsFile('jquery.popup.js');
        $this->addJsFile('jquery.gardenhandleajaxform.js');
        $this->addJsFile('global.js');
        $this->addCssFile('style.css');
        $this->addCssFile('vanillicon.css', 'static');
        $this->addModule('GuestModule');
        parent::initialize();
    }

    /**
     * Adds inform messages to response for inclusion in pages dynamically.
     *
     * @since 2.0.18
     * @access public
     */
    public function inform() {
        $this->deliveryType(DELIVERY_TYPE_BOOL);
        $this->deliveryMethod(DELIVERY_METHOD_JSON);

        // Retrieve all notifications and inform them.
        NotificationsController::informNotifications($this);
        $this->fireEvent('BeforeInformNotifications');

        $this->render();
    }

    /**
     * Grabs all new notifications and adds them to the sender's inform queue.
     *
     * This method gets called by dashboard's hooks file to display new
     * notifications on every pageload.
     *
     * @since 2.0.18
     * @access public
     *
     * @param Gdn_Controller $sender The object calling this method.
     */
    public static function informNotifications($sender) {
        $session = Gdn::session();
        if (!$session->isValid()) {
            return;
        }

        $activityModel = new ActivityModel();
        // Get five pending notifications.
        $where = [
            'NotifyUserID' => Gdn::session()->UserID,
            'Notified' => ActivityModel::SENT_PENDING];

        // If we're in the middle of a visit only get very recent notifications.
        $where['DateUpdated >'] = Gdn_Format::toDateTime(strtotime('-5 minutes'));

        $activities = $activityModel->getWhere($where, '', '', 5, 0)->resultArray();

        $activityIDs = array_column($activities, 'ActivityID');
        $activityModel->setNotified($activityIDs);

        $sender->EventArguments['Activities'] = &$activities;
        $sender->fireEvent('InformNotifications');

        foreach ($activities as $activity) {
            if ($activity['Photo']) {
                $userPhoto = anchor(
                    img($activity['Photo'], ['class' => 'ProfilePhotoMedium']),
                    $activity['Url'],
                    'Icon'
                );
            } else {
                $userPhoto = '';
            }

            $excerpt = '';
            $skipStory = $activity['Data']['skipStory'] ?? false;
            $activityStory = $activity['Story'] ?? null;
            $story = (!$skipStory) ? $activityStory : null;
            $format = $activity['Format'] ?? HtmlFormat::FORMAT_KEY;
            $excerpt = htmlspecialchars($story ? Gdn::formatService()->renderExcerpt($story, $format) : $excerpt);
            $activityClass = ' Activity-'.$activity['ActivityType'];
            $link = $activity['Route'];

            $verifiedIcon='';
            $verified = $activity['Verified'] && $activity['ActivityTypeID'] == 30;

            if($verified) {
                $verifiedIcon = '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.25 9C1.25 4.71979 4.71979 1.25 9 1.25C11.0554 1.25 13.0267 2.06652 14.4801 3.51992C15.9335 4.97333 16.75 6.94457 16.75 9C16.75 13.2802 13.2802 16.75 9 16.75C4.71979 16.75 1.25 13.2802 1.25 9Z" fill="#05BF8E" stroke="#05BF8E" stroke-width="2.5"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.02851 8.40338C5.35801 8.07387 5.89224 8.07387 6.22175 8.40338L8.01161 10.1932L12.188 6.01689C12.5175 5.68739 13.0517 5.68739 13.3812 6.01689C13.7107 6.3464 13.7107 6.88063 13.3812 7.21014L8.60823 11.9831C8.27873 12.3126 7.7445 12.3126 7.41499 11.9831L5.02851 9.59662C4.699 9.26712 4.699 8.73288 5.02851 8.40338Z" fill="white"/>
                </svg>';
            }

            $sender->informMessage(
                '<div class="toast-container">'.$userPhoto.'<div>'
                .wrap($activity['Headline'].$verifiedIcon, 'div', ['class' => 'toast-title'.($verified?' verified':'')])
                .wrap($story, 'div', ['class' => 'toast-text'])
                .'<a href="'.$link.'" class="btn-default">'.t('See').'</a></div></div>',
                'Dismissable AutoDismiss'.$activityClass.($userPhoto == '' ? '' : ' HasIcon')
            );
        }
    }


    // Mark as Read Ajax methods

    public function markAllAsRead() {
        $this->ActivityModel = new ActivityModel();
        $this->ActivityModel->markRead(Gdn::session()->UserID);
        echo 'success';
    }

    public function markSingleRead($ID = 0) {
        $this->ActivityModel = new ActivityModel();
        $this->ActivityModel->markSingleRead($ID);

        $this->userModel = new UserModel();
        $user = $this->userModel->getID(Gdn::session()->UserID);
        if (val('CountNotifications', $user) != 0) {
            $this->userModel->setField(Gdn::session()->UserID, 'CountNotifications', val('CountNotifications', $user) -1);
        }

        echo 'success';
    }
}
