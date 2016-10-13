<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::session();
$desc = t('Routes are used to redirect users.', 'Routes are used to redirect users depending on the URL requested.');
$desc .= ' '.anchor(t('Learn about custom routing.', 'Learn about custom routing.'), 'http://docs.vanillaforums.com/developers/routes');
helpAsset(sprintf(t('About %s'), t('Routes')), $desc);
?>
<div class="header-block">
    <h1><?php echo t('Manage Routes'); ?></h1>
    <div class="btn-group"><?php echo anchor(t('Add Route'), 'dashboard/routes/add', 'js-modal btn btn-primary'); ?></div>
</div>
<div class="table-wrap">
    <table class="table-data js-tj" id="RouteTable">
        <thead>
        <tr>
            <th class="column-lg"><?php echo t('Route'); ?></th>
            <th class="column-lg"><?php echo t('Target'); ?></th>
            <th class="column-md"><?php echo t('Type'); ?></th>
            <th class="options column-sm"></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = 0;
        $Alt = false;
        foreach ($this->MyRoutes as $Route => $RouteData) {
            $Alt = !$Alt;

            $Target = $RouteData['Destination'];
            $RouteType = t(Gdn::router()->RouteTypes[$RouteData['Type']]);
            $Reserved = $RouteData['Reserved'];
            ?>
            <tr<?php echo $Alt ? ' class="Alt"' : ''; ?>>
                <td class="strong"><?php echo $Route; ?></td>
                <td class="Alt"><?php echo $Target; ?></td>
                <td class="Alt"><?php echo $RouteType; ?></td>
                <td class="options">
                    <div class="btn-group">
                    <?php
                    echo anchor(dashboardSymbol('edit'), '/dashboard/routes/edit/'.trim($RouteData['Key'], '='), 'js-modal btn btn-icon', ['aria-label' => t('Edit'), 'title' => t('Edit')]);
                    if (!$Reserved) {
                        echo anchor(dashboardSymbol('delete'), '/routes/delete/'.trim($RouteData['Key'].'=').'/'.$Session->TransientKey(), 'js-modal-confirm btn btn-icon', ['aria-label' => t('Delete'), 'title' => t('Delete')]);
                    }
                    ?>
                    </div>
                </td>
            </tr>
            <?php
            ++$i;
        }
        ?>
        </tbody>
    </table>
</div>
