<?php
/**
 * @package   Nicepage Website Builder
 * @author    Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

class PagesTableParams extends JTable
{
    private $_s = '_';
    /**
     * Constructor
     *
     * @param JDatabaseDriver $db Database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__nicepage_params', 'id', $db);
    }

    /**
     * Get params from table
     *
     * @return array
     */
    public function getParameters()
    {
        if (!$this->load(array('name' => 'com_nicepage'))) {
            $parameters = $this->getAll();
            $result = array();
            if (count($parameters) > 0) {
                $content = '';
                for ($i = 0; $i < count($parameters); $i++) {
                    $param = $parameters[$i];
                    $name = $param['name'];
                    $value = $param['params'];
                    $parts = explode($this->_s, $param['name']);
                    if (count($parts) > 1) {
                        $name = $parts[0];
                    }
                    $content .= $value;
                    $nextIndex = $i + 1;
                    if ($nextIndex < count($parameters) && ($nextParam = $parameters[$nextIndex]) && strpos($nextParam['name'], $this->_s) !== false) {
                        continue;
                    }
                    $result[$name] = json_decode($content);
                    $content = '';
                }
            }
            return $result;
        }
        $registry = new JRegistry();
        $registry->loadString($this->params);
        return $registry->toArray();
    }

    /**
     * Save params to table
     *
     * @param string $params Editor params
     */
    public function saveParameters($params)
    {
        $maxLength = 500 * 1024; //500kb
        if (count($params) > 0) {
            $this->removeAll();
            foreach ($params as $key => $value) {
                $value = json_encode($value);
                $newParams = array($key => $value);
                if (strlen($value) > $maxLength) {
                    $newParams = array();
                    $parts = str_split($value, $maxLength);
                    foreach ($parts as $i => $part) {
                        $newParams[$i !== 0 ? ($key . $this->_s . $i) : $key] = $part;
                    }
                }
                foreach ($newParams as $n => $v) {
                    JTable::getInstance('Params', 'PagesTable')->save(
                        array(
                            'name' => $n,
                            'params' => $v
                        )
                    );
                }
            }
        }
    }

    /**
     * Get all parameters
     *
     * @return mixed
     */
    public function getAll()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($this->_tbl);
        $db->setQuery($query);
        return $db->loadAssocList();
    }

    /**
     * Remove all parameters
     */
    public function removeAll()
    {
        $db = $this->getDbo();
        $db->setQuery('TRUNCATE TABLE ' . $this->_tbl)->execute();
    }
}