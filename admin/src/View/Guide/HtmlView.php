<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\View\Guide;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Administrator\Helper\WaterWaysGuideHelper;

/**
 * Guide Edit View
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The item object
     *
     * @var  object
     */
    protected $item;

    /**
     * The model state
     *
     * @var  object
     */
    protected $state;

    /**
     * Display the view
     *
     * @param   string  $tpl  The template name
     *
     * @return  void
     */
    public function display($tpl = null): void
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar
     *
     * @return  void
     */
    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew = empty($this->item->GuideID);
        $canDo = WaterWaysGuideHelper::getActions();

        $title = $isNew
            ? Text::_('COM_WATERWAYS_GUIDE_GUIDE_NEW')
            : Text::_('COM_WATERWAYS_GUIDE_GUIDE_EDIT');

        ToolbarHelper::title($title, 'location');

        // Save buttons
        if ($canDo->get('core.edit') || $canDo->get('core.create')) {
            ToolbarHelper::apply('guide.apply');
            ToolbarHelper::save('guide.save');
        }

        if ($canDo->get('core.create')) {
            ToolbarHelper::save2new('guide.save2new');
        }

        if (!$isNew && $canDo->get('core.create')) {
            ToolbarHelper::save2copy('guide.save2copy');
        }

        ToolbarHelper::cancel('guide.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
