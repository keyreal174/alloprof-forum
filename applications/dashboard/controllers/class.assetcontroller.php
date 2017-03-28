<?php
/**
 * Manages asset endpoints.
 *
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package Dashboard
 * @since 2.0
 */

/**
 * Handles /asset endpoint.
 */
class AssetController extends DashboardController {
    /**
     * Delete an image from config. Will attempt to remove any element with the an id that is the slugified
     * config concatinated with '-preview-wrapper'.
     *
     * @param string $config The config value to delete.
     * @throws Gdn_UserException
     */
    public function deleteConfigImage($config = '') {
        $imageWrapperId = slugify($config).'-preview-wrapper';

        if (!Gdn::request()->isAuthenticatedPostBack()) {
            throw new Gdn_UserException('The CSRF token is invalid.', 403);
        }
        $this->permission('Garden.Settings.Manage');

        if (!$config) {
            return;
        }

        $config = urldecode($config);

        if (c($config, false) !== false) {
            $upload = new Gdn_UploadImage();
            if ($upload->delete(c($config))) {
                // Fore extra safety, ensure an image has been deleted before removing from config.
                removeFromConfig($config);
                $this->informMessage(t('Image deleted.'));
                $this->jsonTarget('#'.$imageWrapperId, '', 'Remove');
            } else {
                $this->informMessage(t('Error deleting image.'));
            }
        }

        $this->render('blank', 'utility', 'dashboard');
    }
}
