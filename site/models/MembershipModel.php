<?php

// site/models/MembershipModel.php
namespace Waterwaysguide\Component\Waterways_guide\Site\Model;

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseQuery;

class MembershipModel extends ItemModel
{
    protected $_item;

    public function &getItem()
    {
        if (!isset($this->_item)) {
            $cache = Factory::getCache('com_waterways_guide', '');
            $id = $this->getState('membership.id');

            $this->_item =  $cache->get($id);

            if ($this->_item === false) {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('*')
                    ->from($db->quoteName('#__users'))
                    ->where('id = ' . (int) $id);

                $db->setQuery($query);
                if (!$db->execute()) {
                    throw new \Exception($db->getErrorMsg(), 500);
                }

                $this->_item = $db->loadObject();
                $cache->store($this->_item, $id);
            }
        }
        return $this->_item;
    }
}
