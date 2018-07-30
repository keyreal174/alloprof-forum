<?php if (!defined('APPLICATION')) exit();
helpAsset(t('Need More Help?'), anchor(t("Video tutorial on advanced settings"), 'settings/tutorials/category-management-and-advanced-settings'));
?>
<h1><?php echo t('Posting Settings'); ?></h1>

<?php
/** @var Gdn_Form $form */
$form = $this->Form;
$formats = $this->data('formats');
$formatOptions = [];
foreach ($formats as $formatName) {
    $formatOptions[$formatName] = $formatName;
}
echo $form->open();
echo $form->errors();
?>
    <ul>
        <li class="form-group">
            <?php
            $checkboxDesc = 'Checkboxes allow admins to perform batch actions on a number of discussions or comments at the same time.';
            echo $form->toggle('Vanilla.AdminCheckboxes.Use', 'Enable checkboxes on discussions and comments', [], $checkboxDesc);
            ?>
        </li>
        <li class="form-group">
            <?php
            $embedsLabel = 'Enable link embeds in discussions and comments';
            $embedsDesc = 'Allow links to be tranformed into embedded representations in discussions and comments. ';
            $embedsDesc .= 'For example, a YouTube link will transform into an embedded video.';
            echo $form->toggle('Garden.Format.DisableUrlEmbeds', $embedsLabel, [], $embedsDesc, true);
            ?>
        </li>

        <li class="form-group">
            <?php
            $formatDesc = '<p>Select the default format of the editor for posts in the community.</p><p><strong>Note:</strong> the editor will auto-detect the format of old posts when editing them and load their original formatting rules. Aside from this exception, the selected post format below will take precedence.</p>';
            ?>
            <div class="label-wrap">
                <?php
                echo $form->label('Post Format', 'Garden.InputFormatter');
                echo wrap(
                    $formatDesc,
                    'div',
                    ['class' => 'info']
                );
                ?>
            </div>
            <div class="input-wrap">
                <?php echo $form->dropDown('Garden.InputFormatter', $formatOptions); ?>
            </div>
        </li>
        <li class="form-group">
            <?php
            $forceWysiwygLabel = 'Reinterpret All Posts As Wysiwyg';
            $forceWysiwygDesc = '<p class="info">Check the below option to tell the editor to reinterpret all old posts as Wysiwyg.</p> <p class="info"><strong>Note:</strong> This setting will only take effect if Wysiwyg was chosen as the Post Format above. The purpose of this option is to normalize the editor format. If older posts edited with another format, such as markdown or BBCode, are loaded, this option will force Wysiwyg.</p>';
            echo $form->toggle('Plugins.editor.ForceWysiwyg', $forceWysiwygLabel, [], $forceWysiwygDesc);
            ?>
        </li>
        <li class="form-group">
            <?php
            $mobileFormatDesc = '<p>Specify an editing format for mobile devices. If mobile devices should have the same experience, specify the same one as above. If users report issues with mobile editing, this is a good option to change.</p>';
            ?>
            <div class="label-wrap">
                <?php
                echo $form->label('Mobile Format', 'Garden.MobileInputFormatter');
                echo wrap(
                    $mobileFormatDesc,
                    'div',
                    ['class' => 'info']
                );
                ?>
            </div>
            <div class="input-wrap">
                <?php echo $form->dropDown('Garden.MobileInputFormatter', $formatOptions); ?>
            </div>
        </li>


        <li class="form-group">
            <?php
            $Options = ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '0' => 'No limit'];
            $Fields = ['TextField' => 'Code', 'ValueField' => 'Code'];
            ?>
            <div class="label-wrap">
            <?php
            echo $form->label('Maximum Category Display Depth', 'Vanilla.Categories.MaxDisplayDepth');
            echo wrap(
                t('CategoryMaxDisplayDepth.Notes', 'Nested categories deeper than this depth will be placed in a comma-delimited list.'),
                'div',
                ['class' => 'info']
            );
            ?>
            </div>
            <div class="input-wrap">
            <?php echo $form->dropDown('Vanilla.Categories.MaxDisplayDepth', $Options, $Fields); ?>
            </div>
        </li>
        <li class="form-group">
            <?php
            $Options = ['10' => '10', '15' => '15', '20' => '20', '25' => '25', '30' => '30', '40' => '40', '50' => '50', '100' => '100'];
            $Fields = ['TextField' => 'Code', 'ValueField' => 'Code'];
            ?>
            <div class="label-wrap">
            <?php echo $form->label('Discussions per Page', 'Vanilla.Discussions.PerPage'); ?>
            </div>
            <div class="input-wrap">
            <?php echo $form->dropDown('Vanilla.Discussions.PerPage', $Options, $Fields); ?>
            </div>
        </li>
        <li class="form-group">
            <div class="label-wrap">
            <?php echo $form->label('Comments per Page', 'Vanilla.Comments.PerPage'); ?>
            </div>
            <div class="input-wrap">
            <?php echo $form->dropDown('Vanilla.Comments.PerPage', $Options, $Fields); ?>
            </div>
        </li>
        <li class="form-group">
            <?php
            $Options = ['0' => t('Authors may never edit'),
                '350' => sprintf(t('Authors may edit for %s'), t('5 minutes')),
                '900' => sprintf(t('Authors may edit for %s'), t('15 minutes')),
                '3600' => sprintf(t('Authors may edit for %s'), t('1 hour')),
                '14400' => sprintf(t('Authors may edit for %s'), t('4 hours')),
                '86400' => sprintf(t('Authors may edit for %s'), t('1 day')),
                '604800' => sprintf(t('Authors may edit for %s'), t('1 week')),
                '2592000' => sprintf(t('Authors may edit for %s'), t('1 month')),
                '-1' => t('Authors may always edit')];
            $Fields = ['TextField' => 'Text', 'ValueField' => 'Code']; ?>
            <div class="label-wrap">
            <?php
            echo $form->label('Discussion & Comment Editing', 'Garden.EditContentTimeout');
            echo wrap(t('EditContentTimeout.Notes', 'If a user is in a role that has permission to edit content, those permissions will override this.'), 'div', ['class' => 'info']);
            ?>
            </div>
            <div class="input-wrap">
            <?php echo $form->dropDown('Garden.EditContentTimeout', $Options, $Fields); ?>
            </div>
        </li>
        <li class="form-group">
            <div class="label-wrap">
            <?php echo $form->label('Max Comment Length', 'Vanilla.Comment.MaxLength'); ?>
            <div class="info"><?php echo t("It is a good idea to keep the maximum number of characters allowed in a comment down to a reasonable size."); ?></div>
            </div>
            <div class="input-wrap">
            <?php echo $form->textBox('Vanilla.Comment.MaxLength', ['class' => 'InputBox SmallInput']); ?>
            </div>
        </li>
        <li class="form-group">
            <div class="label-wrap">
            <?php echo $form->label('Min Comment Length', 'Vanilla.Comment.MinLength'); ?>
            <div class="info"><?php echo t("You can specify a minimum comment length to discourage short comments."); ?></div>
            </div>
            <div class="input-wrap">
            <?php echo $form->textBox('Vanilla.Comment.MinLength', ['class' => 'InputBox SmallInput']); ?>
            </div>
        </li>
    </ul>
<?php echo $form->close('Save'); ?>
