<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

$items = $this->items;
$pagination = $this->pagination;
$state = $this->state;
$filterForm = $this->getFilterForm();

// Get sort parameters with defaults
$listOrder = $state->get('list.ordering', 'GuideID');
$listDirn = $state->get('list.direction', 'asc');
?>

<h1><?php echo Text::_('COM_WATERWAYS_GUIDE_LIST'); ?></h1>

<form action="<?php echo Route::_('index.php?option=com_waterways_guide&view=wwg'); ?>" method="post" name="adminForm" id="adminForm">
    <?php // echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'filters' => $filterForm)); ?>

    <?php if (!empty($items)) : ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo HTMLHelper::_('grid.sort', 'COM_WATERWAYS_GUIDE_GUIDE_ID', 'GuideID', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'COM_WATERWAYS_GUIDE_GUIDE_UPDATE', 'GuideUpdate', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'COM_WATERWAYS_GUIDE_GUIDE_NAME', 'GuideName', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('grid.sort', 'COM_WATERWAYS_GUIDE_GUIDE_COUNTRY', 'GuideCountry', $listDirn, $listOrder); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item) : ?>
                    <tr>
                        <td><?php echo $item->GuideID; ?></td>
                        <td><?php echo $item->GuideUpdate; ?></td>
                        <td><?php echo $item->GuideName; ?></td>
                        <td><?php echo $item->GuideCountry; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php echo $pagination->getListFooter(); ?>
        </div>
    <?php else : ?>
        <p><?php echo Text::_('COM_WATERWAYS_GUIDE_NO_ITEMS'); ?></p>
    <?php endif; ?>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="option" value="com_waterways_guide" />
    <input type="hidden" name="view" value="wwg" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
