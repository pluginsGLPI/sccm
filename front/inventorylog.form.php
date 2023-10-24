<?php

include('../../../inc/includes.php');

$inventorylog = new PluginSccmInventoryLog();

if (isset($_POST["validate"])) {
    echo "validate";
} else {
    // if id missing
    isset($_GET['id'])
        ? $ID = intval($_GET['id'])
        : $ID = 0;

    // display form
    Html::header(
        PluginSccmInventoryLog::getTypeName(),
        $_SERVER["PHP_SELF"],
        "config",
        PluginSccmMenu::class,
        "sccm_inventorylog"
    );

    $inventorylog->display(['id' => $ID]);
    Html::footer();
}
