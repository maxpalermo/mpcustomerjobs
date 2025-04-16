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

namespace MpSoft\MpCustomerJobs\Controllers\Admin;

require_once _PS_MODULE_DIR_ . 'mpcustomerjobs/models/autoload.php';

use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MpCustomerJobsController extends FrameworkBundleAdminController
{
    const TAB_CLASS_NAME = 'MpCustomerJobsAdminController';

    public function indexAction()
    {
        return $this->render('@Modules/mpcustomerjobs/views/templates/admin/twig/index.html.twig');
    }

    /**
     * Visualizza la tabella dei customer jobs
     * 
     * @Route("/mpcustomerjobs/customer-jobs-list", name="mpcustomerjobs_customer_jobs_list", methods={"GET"})
     * 
     * @return Response
     */
    public function customerJobsListAction()
    {
        // Recupera tutti i customer jobs
        $sql = new \DbQuery();
        $sql->select('id_customer, id_area, id_name')
            ->from(\ModelMpCustomerJobs::$definition['table']);
        $rows = \Db::getInstance()->executeS($sql);

        // Costruisci tabella HTML semplice
        $html = '<h2>Customer Jobs</h2>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr><th>id_customer</th><th>id_area</th><th>id_name</th></tr>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['id_customer']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['id_area']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['id_name']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return new Response($html);
    }
    /**
     * @var LegacyContext
     */
    private $legacyContext;

    /**
     * @var \Module
     */
    private $module;

    /**
     * @var int
     */
    private $id_lang;

    /**
     * Constructor
     *
     * @param LegacyContext $legacyContext
     */
    public function __construct(LegacyContext $legacyContext)
    {
        $this->legacyContext = $legacyContext;
        $this->module = \Module::getInstanceByName('mporderflag');
        $this->id_lang = (int) $this->legacyContext->getContext()->language->id;
    }

    /**
     * Get order flag
     * 
     * @Route("/mporderflag/{id_order}/get-flag", name="mporderflag_get_flag", methods={"GET"})
     * 
     * @param int $id_order Order ID
     * 
     * @return Response
     */
    public function getFlagAction(Request $request, $id_order)
    {
        $result = false;
        // Recupera il record attuale o ne crea uno nuovo
        $record = new \ModelMpOrderFlag($id_order);
        $exists = \Validate::isLoadedObject($record);

        $value = null;
        $result = false;

        if ($exists) {
            $id_order_flag = $record->id_order_flag;
            $record = new \ModelMpOrderFlagItem($id_order_flag);
            $exists = \Validate::isLoadedObject($record);
            if ($exists) {
                $result = true;
                $value = $record;
            }
        }

        // Restituisci una risposta JSON
        return $this->json([
            'status' => (bool) $result,
            'record' => $value,
        ]);
    }

    /**
     * Update order flag
     * 
     * @Route("/mporderflag/{id_order}/update-flag", name="mporderflag_update_flag", methods={"POST"})
     * 
     * @param int $id_order Order ID
     * 
     * @return Response
     */
    public function updateFlagAction(Request $request, $id_order)
    {
        $json_data = file_get_contents('php://input');

        try {
            $data = json_decode($json_data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $th) {
            $data = [];
        }

        $flag = (int) ($data['flag'] ?? 0);
        $result = false;
        // Recupera il record attuale o ne crea uno nuovo
        $record = new \ModelMpOrderFlag($id_order);
        $exists = \Validate::isLoadedObject($record);

        // Imposta i valori necessari
        $record->id_order = (int) $id_order;
        $record->id_employee = (int) $this->legacyContext->getContext()->employee->id;
        $record->id_order_flag = (int) $flag;
        $record->date_add = date('Y-m-d H:i:s');
        $record->date_upd = date('Y-m-d H:i:s');

        try {
            if ($exists) {
                $result = $record->update();
            } else {
                $result = $record->add();
            }
            if (!$result) {
                $message = 'Errore durante l\'aggiornamento';
            } else {
                $message = 'Stato ordine aggiornato con successo';
            }
        } catch (\Throwable $th) {
            $result = false;
            $message = $th->getMessage();
        }

        // Restituisci una risposta JSON
        return $this->json([
            'status' => (bool) $result,
            'message' => $message,
        ]);
    }

    /**
     * Delete flag for an order
     * 
     * @Route("/mporderflag/{id_order}/delete-flag", name="mporderflag_delete_flag", methods={"POST"})
     * 
     * @param int $id_order Order ID
     * 
     * @return Response
     */
    public function deleteFlagAction(Request $request, $id_order)
    {
        $orderFlag = new \ModelMpOrderFlag($id_order);
        if (\Validate::isLoadedObject($orderFlag)) {
            $result = $orderFlag->delete();
        } else {
            $result = true;
        }

        return $this->json([
            'result' => (bool) $result,
            'message' => $result ? 'Flag eliminato con successo' : 'Errore durante l\'eliminazione',
        ]);
    }
}
