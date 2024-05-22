<?php

namespace Joomla\Component\WaterWaysGuide\Administrator\Controller;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;

class WwgController extends FormController
{
    protected $view_list = 'wwg';

    public function edit($key = null, $urlVar = null)
    {
        $app = Factory::getApplication();
        $id = $app->input->getInt('id');
        $app->setUserState('com_waterways_guide.edit.wwg.id', $id);

        $this->setRedirect('index.php?option=com_waterways_guide&view=wwg&layout=edit&id=' . $id);
    }

    public function save($key = null, $urlVar = null)
    {
        $app = Factory::getApplication();
        $data = $app->input->get('jform', [], 'array');

        $model = $this->getModel('Wwg');
        if (!$model->save($data)) {
            $this->setMessage($model->getError(), 'error');
            $this->setRedirect('index.php?option=com_waterways_guide&view=wwg&layout=edit&id=' . $data['GuideID']);
            return false;
        }

        $this->setMessage(\Joomla\CMS\Language\Text::_('COM_WATERWAYS_GUIDE_SAVE_SUCCESS'));
        $this->setRedirect('index.php?option=com_waterways_guide&view=wwg');
        return true;
    }

    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_waterways_guide&view=wwg');
    }
}
