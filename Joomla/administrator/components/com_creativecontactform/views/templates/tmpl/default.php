<?php 
/**
 * Joomla! component Creative Contact Form
 *
 * @version $Id: 2012-04-05 14:30:25 svn $
 * @author creative-solutions.net
 * @package Creative Contact Form
 * @subpackage com_creativecontactform
 * @license GNU/GPL
 *
 */

// no direct access
defined('_JEXEC') or die('Restircted access');

?>
<?php if(JV == 'j2') {//////////////////////////////////////////////////////////////////////////////////////Joomla2.x/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////?>

<?php }elseif(JV == 'j3') {//////////////////////////////////////////////////////////////////////////////////////Joomla3.x/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////?>
<?php 
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$sortFields = $this->getSortFields();

$joomla4 = version_compare(JVERSION, '4', '>=') ? true : false;

if($joomla4) { // Bootstrap 4
	$classes_row = 'row';
	$classes_col = 'col-md-';
} else { // Boostrap 2.3.2
	$classes_row = 'row-fluid';
	$classes_col = 'span';
}

?>
<?php if(!J4) {?>
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<?php }?>
<form action="<?php echo JRoute::_('index.php?option=com_creativecontactform'); ?>" method="post" name="adminForm" id="adminForm">
<div class="<?php echo $classes_row; ?>">

<?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="<?php echo $classes_col;?>2">
		<?php echo $this->sidebar; ?>
	</div>
	<?php if(J4) echo '<div class="'.$classes_col.'10">'; ?>
	<div id="j-main-container" class="j-main-container <?php if(!J4) echo $classes_col.'10';?>">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?> 
		<div id="filter-bar" class="btn-toolbar" <?php if($joomla4) echo 'style="padding: 10px 10px 0 10px;display: block;height: 68px;"'?>>
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH_BY_NAME');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH_BY_NAME'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH_BY_NAME'); ?>" <?php if($joomla4) echo 'style="height:46px;"'?> />
			</div>
			<div class="btn-group pull-left">
				<button class="btn btn-secondary hasTooltip" type="submit" title="<?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH'); ?>"><i class="icon-search"></i></button>
				<button class="btn btn-secondary hasTooltip" type="button" title="<?php echo JText::_('COM_CREATIVECONTACTFORM_RESET'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="<?php if(J4){?>var el1 = document.getElementById('sortTable');var el2 = document.getElementById('directionTable');var val1 = el1.options[el1.selectedIndex].value;var val2 = el2.options[el2.selectedIndex].value;Joomla.tableOrdering(val1,val2,'');<?php }else{?>Joomla.orderTable()<?php }?>">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="<?php if(J4){?>var el1 = document.getElementById('sortTable');var el2 = document.getElementById('directionTable');var val1 = el1.options[el1.selectedIndex].value;var val2 = el2.options[el2.selectedIndex].value;Joomla.tableOrdering(val1,val2,'');<?php }else{?>Joomla.orderTable()<?php }?>">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'st.published', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_CREATIVECONTACTFORM_NAME', 'st.name', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'st.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$n = count($this->items);
			foreach ($this->items as $i => $item) :
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'templates.', true, 'cb', $item->publish_up, $item->publish_down); ?>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<a href="<?php echo JRoute::_('index.php?option=com_creativecontactform&task=template.edit&id='.(int) $item->id); ?>">
								<?php echo $this->escape($item->name); ?>
							</a>
						</div>
					</td>
					<td align="center hidden-phone">
						<?php echo $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
		</table>
		<input type="hidden" name="view" value="templates" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</div>
<?php if(J4) echo '</div>'; ?>
</form>
<?php include (JPATH_BASE.'/components/com_creativecontactform/helpers/footer.php'); ?>
<?php }?>
