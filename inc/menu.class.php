<?php
class PluginSccmMenu
{
    public static function getMenuName()
    {
        return __('Interface - SCCM', 'sccm');
    }

    public static function getTypeName($nb = 0)
    {
        return __('Menu', 'sccm');
    }

    public static function getSearchURL($full = true)
    {
        $url = Plugin::getWebDir('sccm', false);
        return $url . '/front/config.form.php';
    }

    public static function getIcon()
   {
      return "fa-solid fa-dice-d20";
   }


    public static function getMenuContent()
    {

        $links_class = [
            PluginSccmInventoryLog::class,
            PluginSccmConfig::class
        ];

        $links = [];
        foreach ($links_class as $link) {
            $link_text =
                "<span class='d-none d-xxl-block'>" . $link::getTypeName(Session::getPluralNumber()) . "</span>";
            $links["<i class='" . $link::getIcon() . "'></i>$link_text"] = $link::getSearchURL(false);
        }

        $menu = [
            'title'   => self::getMenuName(),
            'page'    => self::getSearchURL(false),
            'icon'    => self::getIcon(),
            'options' => [],
            'links'   => $links,
        ];

        $menu['options']['configuration'] = [
            'title' => PluginSccmConfig::getTypeName(Session::getPluralNumber()),
            'page'  => PluginSccmConfig::getSearchURL(false),
            'icon'  => PluginSccmConfig::getIcon(),
            'links' => $links,

        ];

        $menu['options']['sccm_inventorylog'] = [
            'title' => PluginSccmInventoryLog::getTypeName(Session::getPluralNumber()),
            'page'  => PluginSccmInventoryLog::getSearchURL(false),
            'icon'  => PluginSccmInventoryLog::getIcon(),
            'links' => $links,
        ];
        return $menu;
    }
}
