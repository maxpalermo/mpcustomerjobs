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

/**
 * Ajax controller for returning job names by area (PrestaShop 8)
 */
use Symfony\Component\HttpFoundation\JsonResponse;

class MpCustomerJobsAjaxJobNamesModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $id_area = (int) Tools::getValue('id_area', 0);
        $id_lang = (int) $this->context->language->id;
        $names = [];

        if ($id_area > 0) {
            $sql = new \DbQuery();
            $sql->select('n.id_customer_job_name, nl.name')
                ->from('customer_job_link', 'l')
                ->leftJoin('customer_job_name', 'n', 'n.id_customer_job_name = l.id_customer_job_name')
                ->leftJoin('customer_job_name_lang', 'nl', 'nl.id_customer_job_name = n.id_customer_job_name')
                ->where('l.id_customer_job_area = ' . (int) $id_area)
                ->where('nl.id_lang = ' . (int) $id_lang)
                ->orderBy('nl.name');
            $result = \Db::getInstance()->executeS($sql);
            if ($result) {
                foreach ($result as $row) {
                    $names[] = [
                        'id' => (int) $row['id_customer_job_name'],
                        'name' => $row['name'],
                    ];
                }
            }
        }
        $response = new JsonResponse($names);
        $response->send();
        exit;
    }
}
