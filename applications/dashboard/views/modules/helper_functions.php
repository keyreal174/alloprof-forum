<?php
if (!defined('APPLICATION')) exit();

use Vanilla\Utility\HtmlUtils;

if (!function_exists('writeGradeFilter')) :
    /**
     * Returns discussions grade filtering.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function writeGradeFilter($gradeID) {
        $Session = Gdn::session();
        $form = new Gdn_Form();
        $DefaultGrade = 0;
        if ($Session) {
            $UserID = $Session->UserID;
            $AuthorMetaData = Gdn::userModel()->getMeta($UserID, 'Profile.%', 'Profile.');
            if ($AuthorMetaData['Grade']) {
                $DefaultGrade = $AuthorMetaData['Grade'];
            }
        }

        $fields = c('ProfileExtender.Fields', []);
        if (!is_array($fields)) {
            $fields = [];
        }
        foreach ($fields as $k => $field) {
            if ($field['Label'] == "Grade") {
                $GradeOption = $field['Options'];

                if ($DefaultGrade && $DefaultGrade !== 0) {
                    $DefaultGrade = array_search($DefaultGrade, $GradeOption);
                }
            }
        }

        echo '<div class="FilterMenu__Dropdown">';
        echo '<img src="/themes/alloprof/design/images/icons/grade.svg"/>';
        echo $form->dropDown('GradeDropdown', $GradeOption, array('IncludeNull' => t('Grade'), 'Value' => $gradeID));
        echo '</div>';
    }
endif;

if (!function_exists('writeDiscussionSort')) :
    /**
     * Returns discussions grade filtering.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function writeDiscussionSort($sort) {
        $form = new Gdn_Form();
        $options = [
            'desc' => t('Recent'),
            'asc' => t('Oldest')
        ];

        echo '<div class="FilterMenu__Dropdown">';
        echo '<img src="/themes/alloprof/design/images/icons/sort.svg"/>';
        echo $form->dropDown('DiscussionSort', $options, [ 'Value' => $sort ]);
        echo '</div>';
    }
endif;

if (!function_exists('writeFilterToggle')) :
    /**
     * Returns discussions grade filtering.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function writeFilterToggle($sender, $explanation, $verified) {
        $form  = new Gdn_Form();
        echo '<ul>';
        echo '<li class="form-group">';
        if ($explanation == 'true') {
            // echo $form>toggle('Explanation', t('With explanations only'), [ 'checked' => $explanation ]);
            echo $form->toggle('Explanation', t('With explanations only'), [ 'checked' => $explanation ]);
        } else {
            // echo $form>toggle('Explanation', t('With explanations only'));
            echo $form->toggle('Explanation', t('With explanations only'), [ 'checked' => $explanation ]);
        }
        echo '</li>';
        echo '<li class="form-group">';
        if ($verified == 'true') {
            echo $form->toggle('VerifiedBy', t('Verified by Alloprof only'), [ 'checked' => $verified ]);
        } else {
            echo $form->toggle('VerifiedBy', t('Verified by Alloprof only'));
        }
        echo '</li>';
        echo '</ul>';
    }
endif;
