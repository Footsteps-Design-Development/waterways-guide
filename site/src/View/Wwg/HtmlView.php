<?php

// src/View/Wwg/HtmlView.php
namespace Waterwaysguide\Component\Waterways_guide\Site\View\Wwg;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null)
    {
        // Get the model state, data, and pagination
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Call the parent display method
        parent::display($tpl);
    }
}
