<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\View\Guides;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Administrator\Helper\WaterWaysGuideHelper;

/**
 * Guides List View
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var  \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  \Joomla\CMS\Object\CMSObject
     */
    protected $state;

    /**
     * Form object for filters
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * Active filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * Display the view
     *
     * @param   string  $tpl  The template name
     *
     * @return  void
     */
    public function display($tpl = null): void
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        // Add submenu
        WaterWaysGuideHelper::addSubmenu('guides');

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
        $canDo = WaterWaysGuideHelper::getActions();

        ToolbarHelper::title(Text::_('COM_WATERWAYS_GUIDE_GUIDES_TITLE'), 'location');

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('guide.add');
        }

        if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
            ToolbarHelper::editList('guide.edit');
        }

        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::publish('guides.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('guides.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::archiveList('guides.archive');
        }

        if ($canDo->get('core.delete')) {
            ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'guides.delete');
        }

        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_waterways_guide');
        }
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields(): array
    {
        return [
            'a.GuideID'      => Text::_('COM_WATERWAYS_GUIDE_GUIDE_ID'),
            'a.GuideName'    => Text::_('COM_WATERWAYS_GUIDE_GUIDE_NAME'),
            'a.GuideCountry' => Text::_('COM_WATERWAYS_GUIDE_GUIDE_COUNTRY'),
            'a.GuideWaterway'=> Text::_('COM_WATERWAYS_GUIDE_GUIDE_WATERWAY'),
            'a.GuideStatus'  => Text::_('COM_WATERWAYS_GUIDE_GUIDE_STATUS'),
            'a.GuideUpdate'  => Text::_('COM_WATERWAYS_GUIDE_GUIDE_UPDATE'),
        ];
    }
}
