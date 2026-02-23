<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\View\Guides;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\WaterWaysGuide\Administrator\Helper\WaterWaysGuideHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    public $filterForm;
    public $activeFilters;

    public function display($tpl = null): void
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_WATERWAYS_GUIDE_GUIDES_TITLE'), 'location');

        $canDo = WaterWaysGuideHelper::getActions();

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('guide.add');
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList('guide.edit');
        }

        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::publish('guides.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('guides.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        }

        if ($canDo->get('core.delete')) {
            ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'guides.delete');
        }

        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_waterways_guide');
        }
    }

    protected function getSortFields(): array
    {
        return [
            'a.GuideID'      => Text::_('COM_WATERWAYS_GUIDE_GUIDE_ID'),
            'a.GuideName'    => Text::_('COM_WATERWAYS_GUIDE_GUIDE_NAME'),
            'a.GuideCountry' => Text::_('COM_WATERWAYS_GUIDE_GUIDE_COUNTRY'),
        ];
    }
}
