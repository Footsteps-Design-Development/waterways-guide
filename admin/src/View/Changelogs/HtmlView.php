<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\View\Changelogs;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Administrator\Helper\WaterWaysGuideHelper;

/**
 * Changelogs List View (Read-Only)
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
     * @var  object
     */
    protected $state;

    /**
     * Form object for filters
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * The active filters
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

        $this->addToolbar();

        // Add submenu
        WaterWaysGuideHelper::addSubmenu('changelogs');

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

        ToolbarHelper::title(Text::_('COM_WATERWAYS_GUIDE_CHANGELOGS_TITLE'), 'list');

        if ($canDo->get('core.admin')) {
            ToolbarHelper::preferences('com_waterways_guide');
        }
    }
}
