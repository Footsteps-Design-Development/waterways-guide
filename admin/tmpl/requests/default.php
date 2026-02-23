<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\WaterWaysGuide\Administrator\View\Requests\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
   ->useScript('multiselect');

$user      = Factory::getApplication()->getIdentity();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo Route::_('index.php?option=com_waterways_guide&view=requests'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table itemList" id="requestsList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_WATERWAYS_GUIDE_REQUESTS_TABLE_CAPTION'); ?>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-10">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_MEMBERID', 'a.memberid', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-15">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_COUNTRY', 'country_name', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_WATERWAY', 'a.GuideWaterway', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-15 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_DATE', 'a.GuideRequestDate', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo Text::_('COM_WATERWAYS_GUIDE_HEADING_METHOD'); ?>
                                </th>
                                <th scope="col" class="w-10 text-center">
                                    <?php echo Text::_('JSTATUS'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item) :
                                // Determine status badge class
                                $statusClass = 'bg-secondary';
                                $statusLabel = 'COM_WATERWAYS_GUIDE_REQUEST_STATUS_PENDING';
                                if ($item->GuideRequestStatus === 'approved') {
                                    $statusClass = 'bg-success';
                                    $statusLabel = 'COM_WATERWAYS_GUIDE_REQUEST_STATUS_APPROVED';
                                } elseif ($item->GuideRequestStatus === 'rejected') {
                                    $statusClass = 'bg-danger';
                                    $statusLabel = 'COM_WATERWAYS_GUIDE_REQUEST_STATUS_REJECTED';
                                }
                            ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->memberid, false, 'cid', 'cb', $item->memberid); ?>
                                </td>
                                <td>
                                    <?php echo (int) $item->memberid; ?>
                                </td>
                                <td>
                                    <?php echo $this->escape($item->country_name ?? $item->GuideCountry); ?>
                                </td>
                                <td>
                                    <?php echo $this->escape($item->GuideWaterway); ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->GuideRequestDate ? HTMLHelper::_('date', $item->GuideRequestDate, Text::_('DATE_FORMAT_LC4')) : '-'; ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $this->escape($item->GuideRequestMethod); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo Text::_($statusLabel); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
