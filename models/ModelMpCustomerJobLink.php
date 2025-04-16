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
class ModelMpCustomerJobLink
{
    public $id_customer_job_area;
    public $id_customer_job_name;
    protected $errors = [];
    protected $module;
    protected $context;

    public function __construct($id_customer_job_area = null, $id_customer_job_name = null)
    {
        $this->id_customer_job_area = $id_customer_job_area;
        $this->id_customer_job_name = $id_customer_job_name;
        $this->module = Module::getInstanceByName('mpcustomerjobs');
        $this->context = Context::getContext();
    }

    public static $definition = [
        'table' => 'customer_job_link',
        'primary' => ['id_customer_job_area', 'id_customer_job_name'],
        'multilang' => false,
        'multishop' => false,
        'fields' => [
            'id_customer_job_area' => [
                'type' => ObjectModelCore::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ],
            'id_customer_job_name' => [
                'type' => ObjectModelCore::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ],
        ],
    ];

    public function install()
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id_customer_job_area` INT(11) NOT NULL,
            `id_customer_job_name` INT(11) NOT NULL,
            PRIMARY KEY (`id_customer_job_area`, `id_customer_job_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        return Db::getInstance()->execute($sql);
    }

    public function uninstall()
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $sql = "DROP TABLE IF EXISTS `{$table}`;";

        return Db::getInstance()->execute($sql);
    }

    public function add()
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $sql = "INSERT INTO `{$table}` (`id_customer_job_area`, `id_customer_job_name`) VALUES ({$this->id_customer_job_area}, {$this->id_customer_job_name}) ON DUPLICATE KEY UPDATE `id_customer_job_area` = {$this->id_customer_job_area}, `id_customer_job_name` = {$this->id_customer_job_name};";

        try {
            return Db::getInstance()->execute($sql);
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();

            return false;
        }
    }

    public function update()
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $sql = "UPDATE `{$table}` SET `id_customer_job_area` = {$this->id_customer_job_area}, `id_customer_job_name` = {$this->id_customer_job_name} WHERE `id_customer_job_area` = {$this->id_customer_job_area} AND `id_customer_job_name` = {$this->id_customer_job_name};";

        try {
            return Db::getInstance()->execute($sql);
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();

            return false;
        }
    }

    public function delete()
    {
        $table = _DB_PREFIX_ . self::$definition['table'];
        $sql = "DELETE FROM `{$table}` WHERE `id_customer_job_area` = {$this->id_customer_job_area} AND `id_customer_job_name` = {$this->id_customer_job_name};";

        try {
            return Db::getInstance()->execute($sql);
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();

            return false;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public static function getList()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_customer_job_area, id_customer_job_name')
            ->from(ModelMpCustomerJobLink::$definition['table'])
            ->orderBy('id_customer_job_area, id_customer_job_name');

        $result = $db->executeS($sql);
        if ($result) {
            $list = [];
            foreach ($result as $row) {
                $list[$row['id_customer_job_area']][] = $row['id_customer_job_name'];
            }

            return $list;
        }

        return [];
    }
}
