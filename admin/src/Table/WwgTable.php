<?php

namespace Joomla\Component\WaterWaysGuide\Administrator\Table;

use Joomla\CMS\Table\Table;

class WwgTable extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__waterways_guide', 'GuideID', $db);
    }
}
