<?php

namespace Joomla\Component\WaterWaysGuide\Administrator\View\Wwg;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\WaterWaysGuide\Administrator\Helper\WaterWaysGuideHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterForm;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm'); // Ensure filter form is initialized

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        // Set the document title
        $document = Factory::getDocument();
        $document->setTitle(Text::_('COM_WATERWAYS_GUIDE_MANAGER'));

        // Call the parent display to render the layout
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_WATERWAYS_GUIDE_MANAGER'), 'address water-ways-guide');

        // We are intentionally not adding the "New" and "Edit" buttons.
        if (WaterWaysGuideHelper::getActions()->get('core.admin')) {
            ToolbarHelper::preferences('com_waterways_guide');
        }
    }

    protected function getSortFields()
    {
        return array(
            'GuideID' => Text::_('COM_WATERWAYS_GUIDE_GUIDE_ID'),
            'GuideName' => Text::_('COM_WATERWAYS_GUIDE_GUIDE_NAME'),
            'GuideUpdate' => Text::_('COM_WATERWAYS_GUIDE_GUIDE_UPDATE'),
            'GuideCountry' => Text::_('COM_WATERWAYS_GUIDE_GUIDE_COUNTRY'),
        );
    }

    public function getFilterForm()
    {
        return $this->filterForm;
    }
}
