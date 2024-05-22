<?php
defined('_JEXEC') or die;

$item = $this->item;
$form = $this->get('Form');
?>

<h1>Edit Guide</h1>

<form action="<?php echo JRoute::_('index.php?option=com_waterways_guide&task=wwg.save'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="form-horizontal">
        <fieldset class="adminform">
            <legend><?php echo JText::_('Guide Details'); ?></legend>
            <div class="row">
                <div class="col-sm-6">
                    <?php echo $form->renderField('GuideID'); ?>
                    <?php echo $form->renderField('GuideName'); ?>
                    <?php echo $form->renderField('GuideSummary'); ?>
                    <!-- Add more fields as needed -->
                </div>
            </div>
        </fieldset>
    </div>

    <div class="btn-toolbar">
        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                <span class="icon-ok"></span> <?php echo JText::_('JSAVE'); ?>
            </button>
        </div>
        <div class="btn-group">
            <a class="btn" href="<?php echo JRoute::_('index.php?option=com_waterways_guide&task=wwg.cancel'); ?>">
                <span class="icon-cancel"></span> <?php echo JText::_('JCANCEL'); ?>
            </a>
        </div>
    </div>

    <input type="hidden" name="task" value="wwg.save" />
    <?php echo JHtml::_('form.token'); ?>
</form>
