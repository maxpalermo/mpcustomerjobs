<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpCustomerJobs\Helpers;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GetActionUrl
{
    protected $module_name;
    protected $admin_module_name;
    protected $context;

    public function __construct($module_name, $admin_module_name)
    {
        $this->module_name = $module_name;
        $this->admin_module_name = $admin_module_name;
        $this->context = \Context::getContext();
    }

    public function getActionUrl($action, $params = [], $admin = true)
    {
        // trasformo $action in lowercase e snake
        $action = \Tools::toUnderscoreCase($action);

        // Ottieni il container dei servizi
        $container = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();

        if ($container !== null) {
            // Genera l'URL completo
            if ($admin) {
                $action = 'admin_' . $action;
            }

            return $container->get('router')->generate("{$this->module_name}_{$action}", $params, UrlGeneratorInterface::ABSOLUTE_URL);
        }

        // Fallback se il container non è disponibile
        return $this->context->link->getAdminLink("{$this->admin_module_name}", true, [], ['action' => $action, 'params' => $params]);
    }
}
