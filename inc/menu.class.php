<?php

/**
 * -------------------------------------------------------------------------
 * Cloudinventory plugin for GLPI
 * Copyright (C) 2022 by the Cloudinventory Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
 */

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
