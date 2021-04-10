<?php
if (!defined('APPLICATION')) exit();

use Vanilla\Utility\HtmlUtils;

if (!function_exists('writeSubjectFilter')) :
    /**
     * Returns discussions subject filtering.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function writeSubjectFilter($subject) {
        $Session = Gdn::session();
        $form = new Gdn_Form();
        $options = [];
        $Categories = CategoryModel::instance()->getFull()->resultArray();
        foreach ($Categories as $category) {
            $options[$category['CategoryID']] = $category['Name'];
        }

        $options = ['Value' => $subject, 'IncludeNull' => true, 'AdditionalPermissions' => ['PermsDiscussionsAdd']];
        echo '<div class="FilterMenu__Dropdown">';
        echo '<img src="'.url("/themes/alloprof/design/images/icons/subject.svg").'"/>';
        echo $form->dropDown('SubjectDropdown', $options, array('IncludeNull' => t('Material'), 'Value' => $subject));
        echo '</div>';
    }
endif;

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
        echo '<img src="'.url("/themes/alloprof/design/images/icons/grade.svg").'"/>';
        echo $form->dropDown('GradeDropdown', $GradeOption, array('IncludeNull' => true, 'Value' => $gradeID));
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
        echo '<img src="'.url("/themes/alloprof/design/images/icons/sort.svg").'"/>';
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
    function writeFilterToggle($explanation, $verified) {
        $form  = new Gdn_Form();
        $role = getUserRole(Gdn::session()->User->UserID);
        $text = $role === 'Teacher' ? t('Without explanations only') : t('With explanations only');
        $verifiedText = $role === 'Teacher' ? t('Not Verified by Alloprof only') : t('Verified by Alloprof only');

        echo '<ul>';
        echo '<li class="form-group">';
        if ($explanation == 'true') {
            echo $form->toggle('Explanation', $text, [ 'checked' => $explanation ]);
        } else {
            echo $form->toggle('Explanation', $text);
        }
        echo '</li>';

        if ($role != 'Teacher') {
            echo '<li class="form-group">';
            if ($explanationout == 'true') {
                echo Gdn::controller()->Form->toggle(($isMobile?'Mobile':'').'OutExplanation', t('Without explanations only'), [ 'checked' => $explanation ]);
            } else {
                echo Gdn::controller()->Form->toggle(($isMobile?'Mobile':'').'OutExplanation', t('Without explanations only'));
            }
            echo '</li>';
        }

        echo '<li class="form-group">';
        if ($verified == 'true') {
            echo $form->toggle('VerifiedBy', $verifiedText, [ 'checked' => $verified ]);
        } else {
            echo $form->toggle('VerifiedBy', $verifiedText);
        }
        echo '</li>';
        echo '</ul>';
    }
endif;

if (!function_exists('getUserRole')) :
    function getUserRole($UserID = null) {
        $userModel = Gdn::userModel();
        if ($UserID) {
            $User = $userModel->getID($UserID);
        } else {
            $User = $userModel->getID(Gdn::session()->UserID);
        }

        if($User) {
            $RoleData = $userModel->getRoles($User->UserID);

            $RoleData = $userModel->getRoles($User->UserID);
            if ($RoleData !== false) {
                $Roles = array_column($RoleData->resultArray(), 'Name');
            }

            // Hide personal info roles
            if (!checkPermission('Garden.PersonalInfo.View')) {
                $Roles = array_filter($Roles, 'RoleModel::FilterPersonalInfo');
            }

            if(in_array(Gdn::config('Vanilla.ExtraRoles.Teacher'), $Roles))
                $UserRole = Gdn::config('Vanilla.ExtraRoles.Teacher') ?? 'Teacher';
            else $UserRole = RoleModel::TYPE_MEMBER ?? 'Student';

            return $UserRole;
        } else return null;
    }
endif;
