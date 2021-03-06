<?php
/**
 * Categories module
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Vanilla
 * @since 2.0
 */

/**
 * Renders the discussion categories.
 */
class CategoriesModule extends Gdn_Module {

    /** @var int Inclusive. */
    public $startDepth = 1;

    /** @var int Inclusive. */
    public $endDepth;

    /** @var bool Whether or not to collapse categories that contain other categories. */
    public $collapseCategories = true;

    /**
     * @var int|null The ID of the root category.
     */
    public $root = null;

    /** @var bool Caring about if we are on top level categories. */
    public $topLevelCategoryOnly = true;

    public function __construct($sender = '') {
        parent::__construct($sender);
        $this->_ApplicationFolder = 'vanilla';

        $this->Visible = c('Vanilla.Categories.Use') && !c('Vanilla.Categories.HideModule');
    }

    public function assetTarget() {
        return 'Panel';
    }

    public function isFollowingCategory($followingCategories, $category) {
        foreach ($followingCategories as $element) {
            if ($element["CategoryID"] == $category["CategoryID"]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the data for this module.
     */
    protected function getData() {
        // Allow plugins to set different data.
        $this->fireEvent('GetData');
        if ($this->Data) {
            return;
        }

        $categoryModel = new CategoryModel();
        $categories = $categoryModel
            ->setJoinUserCategory(true)
            ->getChildTree($this->root, ['collapseCategories' => $this->collapseCategories]);
        $categories = CategoryModel::flattenTree($categories);

        $categories = array_filter($categories, function ($category) {
            $language = Gdn::config('Garden.Locale') == 'fr_CA' ? 'fr' : 'en';
            return val('PermsDiscussionsView', $category) && val('Following', $category) && val('Language', $category) == $language;
        });


        $userCategories = $categoryModel->getFollowed(Gdn::session()->UserID);

        foreach ($categories as $key => $value) {
            # code...
            if ($this->isFollowingCategory($userCategories, $value)) {
                $categories[$key]["isFollowing"] = 1;
            } else {
                $categories[$key]["isFollowing"] = 0;
            }
        }

        function cmp($a, $b) {
            if ($a["isFollowing"] > $b["isFollowing"]) {
                return -1;
            } else if ($a["isFollowing"] < $b["isFollowing"]) {
                return 1;
            } else return 0;
        }

        usort($categories, "cmp");

        $nextButton = array('0' => array('PhotoUrl' => url("/themes/alloprof/design/images/icons/next-slide.png"), 'CategoryID' => 0, 'Name' => 'next'));
        $prevButton = array('0' => array('PhotoUrl' => url("/themes/alloprof/design/images/icons/prev-slide.png"), 'CategoryID' => 0, 'Name' => 'prev'));

        $index = 5;

        while ($index < count($categories) - 1) {
            # code...
            array_splice( $categories, $index, 0, $nextButton );
            $index += 1;
            if ($index <= count($categories)) {
                array_splice( $categories, $index, 0, $prevButton );
                $index += 5;
            }
        }

        $newCategorySet = array();
        $newCategorySet = array_chunk($categories, 6);

        $data = new Gdn_DataSet($newCategorySet, DATASET_TYPE_ARRAY);
        $data->datasetType(DATASET_TYPE_OBJECT);
        $this->Data = $data;
    }

    public function filterDepth(&$categories, $startDepth, $endDepth) {
        if ($startDepth != 1 || $endDepth) {
            foreach ($categories as $i => $category) {
                if (val('Depth', $category) < $startDepth || ($endDepth && val('Depth', $category) > $endDepth)) {
                    unset($categories[$i]);
                }
            }
        }
    }

    public function toString() {
        if (!$this->Data) {
            $this->getData();
        }

        /** @psalm-suppress InvalidPassByReference */
        $this->filterDepth($this->Data->result(), $this->startDepth, $this->endDepth);

        return parent::toString();
    }
}
