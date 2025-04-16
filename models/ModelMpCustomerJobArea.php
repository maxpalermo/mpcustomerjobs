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
class ModelMpCustomerJobArea extends ObjectModel
{
    public $name;
    public $date_add;
    public $date_upd;

    protected $errors = [];

    public static $definition = [
        'table' => 'customer_job_area',
        'primary' => 'id_customer_job_area',
        'multilang' => true,
        'multishop' => false,
        'fields' => [
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 64,
                'required' => true,
                'lang' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'datetime' => true,
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'datetime' => true,
                'required' => false,
            ],
        ],
    ];

    public function getErrors()
    {
        return $this->errors;
    }

    public function install()
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id_customer_job_area` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(64) NOT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_customer_job_area`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $exec = Db::getInstance()->execute($sql);

        $sql_lang = "CREATE TABLE IF NOT EXISTS `{$table}_lang` (
            `id_customer_job_area` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `name` VARCHAR(64) NOT NULL,
            PRIMARY KEY (`id_customer_job_area`, `id_lang`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $exec_lang = Db::getInstance()->execute($sql_lang);

        return $exec && $exec_lang;
    }

    public function uninstall()
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $sql = "DROP TABLE IF EXISTS `{$table}`;";
        $sql_lang = "DROP TABLE IF EXISTS `{$table}_lang`;";

        return Db::getInstance()->execute($sql) && Db::getInstance()->execute($sql_lang);
    }

    public static function getList()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_customer_job_area, name')
            ->from(ModelMpCustomerJobArea::$definition['table'] . '_lang')
            ->where('id_lang = ' . (int) Context::getContext()->language->id)
            ->orderBy('name');

        $result = $db->executeS($sql);
        if ($result) {
            $list = [];
            foreach ($result as $row) {
                $list[$row['id_customer_job_area']] = Tools::strtoupper($row['name']);
            }

            return $list;
        }

        return [];
    }
}
