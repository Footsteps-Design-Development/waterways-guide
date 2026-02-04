<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\View\Wwg;

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
    protected $filterForm;

    public function display($tpl = null): void
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        // Set the document title
        $this->getDocument()->setTitle(Text::_('COM_WATERWAYS_GUIDE_MANAGER'));

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_WATERWAYS_GUIDE_MANAGER'), 'address water-ways-guide');

        if (WaterWaysGuideHelper::getActions()->get('core.admin')) {
            ToolbarHelper::preferences('com_waterways_guide');
        }
    }

    protected function getSortFields(): array
    {
        return [
            'GuideID' => Text::_('COM_WATERWAYS_GUIDE_GUIDE_ID'),
            'GuideName' => Text::_('COM_WATERWAYS_GUIDE_GUIDE_NAME'),
            'GuideUpdate' => Text::_('COM_WATERWAYS_GUIDE_GUIDE_UPDATE'),
            'GuideCountry' => Text::_('COM_WATERWAYS_GUIDE_GUIDE_COUNTRY'),
        ];
    }

    public function getFilterForm()
    {
        return $this->filterForm;
    }
}
