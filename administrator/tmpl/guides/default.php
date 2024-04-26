<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Waterways_guide
 * @author     Russell English <russell@footsteps-design.co.uk>
 * @copyright  2024 Russell English
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;


use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

// Import CSS
$wa =  $this->document->getWebAssetManager();
$wa->useStyle('com_waterways_guide.admin')
	->useScript('com_waterways_guide.admin');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_waterways_guide');

$saveOrder = $listOrder == 'a.ordering';

if (!empty($saveOrder)) {
	$saveOrderingUrl = 'index.php?option=com_waterways_guide&task=guides.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>

<form action="<?php echo Route::_('index.php?option=com_waterways_guide&view=guides'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
				<table class="table table-striped" id="guideList">
					<thead>
						<tr>
							<th class="w-1 text-center">
								<input type="checkbox" autocomplete="off" class="form-check-input" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>

							<?php if (isset($this->items[0]->ordering)) : ?>
								<th scope="col" class="w-1 text-center d-none d-md-table-cell">

									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>

								</th>
							<?php endif; ?>


							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>

							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_GUIDEID', 'a.GuideID', $listDirn, $listOrder); ?>
							</th>

							<th scope="col">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_GUIDENAME', 'a.GuideName', $listDirn, $listOrder); ?>
							</th>




						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody <?php if (!empty($saveOrder)) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" <?php endif; ?>>
						<?php foreach ($this->items as $i => $item) :
							$ordering   = ($listOrder == 'a.ordering');
							$canCreate  = $user->authorise('core.create', 'com_waterways_guide');
							$canEdit    = $user->authorise('core.edit', 'com_waterways_guide');
							$canCheckin = $user->authorise('core.manage', 'com_waterways_guide');
							$canChange  = $user->authorise('core.edit.state', 'com_waterways_guide');
							$link = Route::_('index.php?option=com_waterways_guide&task=guide.edit&id=' . (int) $item->GuideID);
						?>
							<tr class="row<?php echo $i % 2; ?>" data-draggable-group='1' data-transition>
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->GuideID); ?>
								</td>

								<?php if (isset($this->items[0]->ordering)) : ?>

									<td class="text-center d-none d-md-table-cell">

										<?php

										$iconClass = '';

										if (!$canChange) {
											$iconClass = ' inactive';
										} elseif (!$saveOrder) {
											$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
										}							?> <span class="sortable-handler<?php echo $iconClass ?>">
											<span class="icon-ellipsis-v" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
										<?php endif; ?>
									</td>
								<?php endif; ?>

								<td class="text-center">
									<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'guides.', $canChange, 'cb'); ?>
								</td>

								<td class="text-center">
									<?php echo htmlspecialchars($item->GuideID, ENT_COMPAT, 'UTF-8'); ?>
								</td>
								<td>
									<?php if ($canEdit) : ?>
										<a href="<?php echo $link; ?>">
											<?php echo htmlspecialchars($item->GuideName, ENT_COMPAT, 'UTF-8'); ?>
										</a>
									<?php else : ?>
										<?php echo htmlspecialchars($item->GuideName, ENT_COMPAT, 'UTF-8'); ?>
									<?php endif; ?>
								</td>


							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>