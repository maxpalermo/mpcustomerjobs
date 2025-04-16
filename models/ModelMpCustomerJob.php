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
class ModelMpCustomerJob extends ObjectModel
{
    public $id_customer;
    public $id_customer_job_area;
    public $id_customer_job_name;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'customer_job',
        'primary' => 'id_customer',
        'multilang' => false,
        'multishop' => false,
        'fields' => [
            'id_customer_job_area' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'id_customer_job_name' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'datetime' => true,
                'required' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'datetime' => true,
                'required' => false,
            ],
        ],
    ];

    public static function getCustomerJob($id_customer)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('a.id_customer, b.id_customer_job_area, c.id_customer_job_name, bl.name as job_area, cl.name as job_name')
            ->from(ModelMpCustomerJob::$definition['table'], 'a')
            ->leftJoin(ModelMpCustomerJobArea::$definition['table'], 'b', 'a.id_customer_job_area = b.id_customer_job_area')
            ->leftJoin(ModelMpCustomerJobName::$definition['table'], 'c', 'a.id_customer_job_name = c.id_customer_job_name')
            ->leftJoin(ModelMpCustomerJobArea::$definition['table'] . '_lang', 'bl', 'b.id_customer_job_area = bl.id_customer_job_area')
            ->leftJoin(ModelMpCustomerJobName::$definition['table'] . '_lang', 'cl', 'c.id_customer_job_name = cl.id_customer_job_name')
            ->where('a.id_customer = ' . (int) $id_customer);

        return $db->getRow($sql);
    }

    public function install()
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id_customer` INT(11) NOT NULL AUTO_INCREMENT,
            `id_customer_job_area` INT(11) NOT NULL,
            `id_customer_job_name` INT(11) NOT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_customer`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $exec = Db::getInstance()->execute($sql);

        return $exec;
    }

    public function uninstall()
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $sql = "DROP TABLE IF EXISTS `{$table}`;";

        return Db::getInstance()->execute($sql);
    }
}
