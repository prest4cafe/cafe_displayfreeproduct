<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class cafe_displayfreeproduct extends Module
{
    public function __construct()
    {
        $this->name = 'cafe_displayfreeproduct';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'presta.cafe';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('cafe_displayfreeproduct');
        $this->description = $this->l('Display free product instead price');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() &&
        $this->registerHook('filterProductContent') &&
        $this->registerHook('actionOutputHTMLBefore') &&
        $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $output;
    }

    public function hookFilterProductContent(array $params)
    {
        $params['object']['price'] = $this->_updateContentVars($params['object']['price'], $params);

        return [
            'object' => $params['object']
        ];
    }

    public function hookActionOutputHTMLBefore(array $params)
    {
        $page = Dispatcher::getInstance()->getController();

        if ($page == 'category') {
            $params['html'] = $this->_updateContentVars($params['html'], $params);

            return true;
        }
    }

    protected function _updateContentVars($content, $params)
    {
        $content = urldecode($content);

        $regex = '/(?J)(?<num>[0-9]*[.,]?[0-9]+)\s*(?<cur>\p{Sc})|(?<cur>\p{Sc})\s*(?<num>[0-9]*[.,]?[0-9]+)/u';

        preg_match_all($regex, $content, $prices); ///^\d+(\.\d{2})?€/

        if (isset($prices) && sizeof($prices)) {
            foreach ($prices[1] as $price) {
                if ($price =='0,00') {
                    $value='GRATUIT';
                    $content = preg_replace($regex, $value, $content);
                }
            }
        }

        return $content;
    }

    public function hookDisplayHeader($params)
    {
        $page = Dispatcher::getInstance()->getController();

        if ($page == 'product') {
            Media::addJsDef(['free' => 'GRATUIT']);
            $this->context->controller->addJS(($this->_path).'views/js/cafe_displayfreeproduct.js');
        } else {
            return false;
        }
    }
}
