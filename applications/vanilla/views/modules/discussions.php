<?php if (!defined('APPLICATION')) exit();
require_once $this->fetchViewLocation('helper_functions');

if (!isset($this->Prefix))
    $this->Prefix = 'Discussion';
?>
<div class="Box BoxDiscussions">
<?php
if ($this->showTitle) {
    echo panelHeading(t($this->title ?? 'Recent questions'));
}
?>
    <ul class="PanelInfo PanelDiscussions DataList">
        <?php
        foreach ($this->data('Discussions')->result() as $Discussion) {
            writeModuleDiscussion($Discussion, $this->Prefix, $this->getShowPhotos());
        }
        if ($this->data('Discussions')->numRows() >= $this->Limit) {
            ?>
            <li class="ShowAll"><?php echo anchor(t('More…'), 'discussions', '', ['aria-label' => strtolower(sprintf(t('%s discussions'), t('View all')))]); ?></li>
        <?php } ?>
    </ul>
</div>
