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

$joomla4 = version_compare(JVERSION, '4', '>=') ? true : false;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

if($joomla4) {
	$wa = $this->document->getWebAssetManager();
	$wa->useScript('multiselect');
}

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;

if(true) {

$saveOrder	= $listOrder == 'sp.ordering';
if ($saveOrder)
{
	if($joomla4) {
		$saveOrderingUrl = 'index.php?option=com_creativecontactform&task=creativefields.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
		HTMLHelper::_('draggablelist.draggable');
	} else {
		$saveOrderingUrl = 'index.php?option=com_creativecontactform&task=creativefields.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
	}
}
$sortFields = $this->getSortFields();

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
		<div id="filter-bar" class="btn-toolbar" <?php if($joomla4) echo 'style="padding: 10px 10px 10px 10px;display: block;"'?>>
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH_BY_NAME');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH_BY_NAME'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH_BY_NAME'); ?>" <?php if($joomla4) echo 'style="height:46px;"'?> />
			</div>
			<div class="btn-group pull-left">
				<button class="btn btn-secondary hasTooltip" type="submit" title="<?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH'); ?>"><i class="icon-search"></i></button>
				<button class="btn btn-secondary hasTooltip" type="button" title="<?php echo JText::_('COM_CREATIVECONTACTFORM_RESET'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-left">
				<div class="col_info_wrapper" <?php if($joomla4) echo 'style="margin-top: 15px;margin-left: 5px;"'?>>
					<span class="col_0_preview"></span><span class="col_pr_name">Both Columns</span>
					<span class="col_1_preview"></span><span class="col_pr_name">Left Column</span>
					<span class="col_2_preview"></span><span class="col_pr_name">Right Column</span>
					<span class="col_none_preview"></span><span class="col_pr_name">Popup</span>
					<span class="col_pagebreak_preview"></span><span class="col_pr_name">Page Break</span>
				</div>
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
				<select name="sortTable" id="sortTable" class="input-medium" onchange="<?php if(J4){?>
					var el1 = document.getElementById('sortTable');
					var el2 = document.getElementById('directionTable');
					var val1 = el1.options[el1.selectedIndex].value;
					var val2 = el2.options[el2.selectedIndex].value;
					Joomla.tableOrdering(val1,val2,'');
					<?php }else{?>Joomla.orderTable()<?php }?>">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'sp.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'sp.published', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_CREATIVECONTACTFORM_NAME', 'sp.name', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'COM_CREATIVECONTACTFORM_TYPE', 'type', $listDirn, $listOrder); ?>
					</th>
					<th width="10%"><?php echo JText::_('COM_CREATIVECONTACTFORM_COLUMN_TYPE_LABEL1');?></th>
					<th width="20%">
						<?php echo JHtml::_('grid.sort', 'COM_CREATIVECONTACTFORM_FORM', 'form', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'sp.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody <?php if ($saveOrder && $joomla4) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
			<?php
			$n = count($this->items);
			foreach ($this->items as $i => $item) :
				$ordering	= $listOrder == 'sp.ordering';
				// $col_color = $item->type != 'Creative Popup' ? $item->column_type : 'none';
				if($item->type == 'Creative Popup' || $item->type == 'If-Then')
					$col_color = 'none';
				elseif($item->type == 'Page Break')
					$col_color = 'pagebreak';
				else
					$col_color = $item->column_type;
				?>

				<tr class=" ccf_column_<?php echo $col_color;?>" data-draggable-group="7">
					<td class="text-center d-none d-md-table-cell">
						<?php
							$disableClassName = '';
							$disabledLabel	  = '';
							if (!$saveOrder) :
								$disabledLabel    = JText::_('JORDERINGDISABLED');
								$disableClassName = 'inactive tip-top';
							endif; ?>
							<span class="sortable-handler hasTooltip<?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
								<?php $icon_class = J4 ? 'icon-ellipsis-v' : 'icon-menu';?>
								<span class="<?php echo $icon_class; ?>" aria-hidden="true"></span>
							</span>
							<input type="text" style="display:none" name="order[]" size="5"
							value="<?php echo $item->ordering;?>" class="width-20 text-area-order hidden" />
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'creativefields.', true, 'cb', $item->publish_up, $item->publish_down); ?>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<a href="<?php echo JRoute::_('index.php?option=com_creativecontactform&task=creativefield.edit&id='.(int) $item->id); ?>">
								<?php echo $this->escape($item->name); ?>
							</a>
						</div>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<?php echo $this->escape($item->type); ?>
						</div>
					</td>
					<td align="center hidden-phone">
						<div style="padding-right: 50px;white-space: nowrap;">
						<?php 
						$col_array = array(0=>JText::_('COM_CREATIVECONTACTFORM_COLUMN_TYPE_BOTH'),1=>JText::_('COM_CREATIVECONTACTFORM_COLUMN_TYPE_1'),2=>JText::_('COM_CREATIVECONTACTFORM_COLUMN_TYPE_2'));
						$col_txt = $item->type != 'Creative Popup' && $item->type != 'Page Break' && $item->type != 'If-Then' ? $col_array[$item->column_type] : 'None';
						echo $col_txt; ?>
						</div>
					</td>
					<td align="small hidden-phone">
						<div style="padding-right: 50px;white-space: nowrap;">
						<a href="<?php echo JRoute::_('index.php?option=com_creativecontactform&task=creativeform.edit&id='.(int) $item->form_id); ?>">
							<?php echo $item->form; ?>
						</a></div>
					</td>
					<td align="center hidden-phone">
						<?php echo $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="11">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
		</table>
		<input type="hidden" name="view" value="creativefields" />
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

<style>
.table th, .table td {
border-top: 1px solid #C2C2C2 !important;
}
tr.ccf_column_0 td {
	background-color: rgba(255, 199, 135, 0.46) !important;
}
tr.ccf_column_1 td {
	background-color: rgba(121, 210, 255, 0.35) !important;
}
tr.ccf_column_2 td {
	background-color: rgba(127, 255, 138, 0.53) !important;
}
tr.ccf_column_none td {
	background-color: rgba(0, 0, 0, 0.2) !important;
}
tr.ccf_column_pagebreak td {
	background-color: rgba(224, 0, 0, 0.33) !important;
}
.col_info_wrapper {
	font-size: 12px !important;
	margin-top: 6px;
}
.col_0_preview {
	display: inline-block;
	width: 20px;
	height: 18px;
	margin-top: -1px;
	padding: 0 5px 0 5px;
	background-color: rgba(255, 199, 135, 0.46) !important;
	border: 1px solid #C2C2C2 !important;
	float: left;
	cursor: pointer;
	transition: all 0.2s ease-out 0s;
	-webkit-transition: all 0.2s ease-out 0s;
	-moz-transition: all 0.2s ease-out 0s;
}
.col_1_preview {
	display: inline-block;
	width: 20px;
	height: 18px;
	margin-top: -1px;
	padding: 0 5px 0 5px;
	background-color: rgba(121, 210, 255, 0.35) !important;
	border: 1px solid #C2C2C2 !important;
	float: left;
	margin-left: 5px;
	cursor: pointer;
	transition: all 0.2s ease-out 0s;
	-webkit-transition: all 0.2s ease-out 0s;
	-moz-transition: all 0.2s ease-out 0s;
}
.col_2_preview {
	display: inline-block;
	width: 20px;
	height: 18px;
	margin-top: -1px;
	padding: 0 5px 0 5px;
	background-color: rgba(127, 255, 138, 0.53) !important;
	border: 1px solid #C2C2C2 !important;
	float: left;
	margin-left: 5px;
	cursor: pointer;
	transition: all 0.2s ease-out 0s;
	-webkit-transition: all 0.2s ease-out 0s;
	-moz-transition: all 0.2s ease-out 0s;
}
.col_none_preview {
	display: inline-block;
	width: 20px;
	height: 18px;
	margin-top: -1px;
	padding: 0 5px 0 5px;
	background-color: rgba(0, 0, 0, 0.2) !important;
	border: 1px solid #A8A8A8 !important;
	float: left;
	margin-left: 5px;
	cursor: pointer;
	transition: all 0.2s ease-out 0s;
	-webkit-transition: all 0.2s ease-out 0s;
	-moz-transition: all 0.2s ease-out 0s;
}
.col_pagebreak_preview {
	display: inline-block;
	width: 20px;
	height: 18px;
	margin-top: -1px;
	padding: 0 5px 0 5px;
	background-color: rgba(224, 0, 0, 0.33) !important;
	border: 1px solid #A8A8A8 !important;
	float: left;
	margin-left: 5px;
	cursor: pointer;
	transition: all 0.2s ease-out 0s;
	-webkit-transition: all 0.2s ease-out 0s;
	-moz-transition: all 0.2s ease-out 0s;
}
.col_0_preview:hover,
.col_1_preview:hover,
.col_2_preview:hover,
.col_pagebreak_preview:hover,
.col_none_preview:hover {
	transform: scale(1.2,1.2);
	-webkit-transform: scale(1.2,1.2);
	-moz-transform: scale(1.2,1.2);
}

.col_pr_name {
	display: inline-block;
	float: left;
	margin-left: 3px;
}
.form-horizontal div.controls {
	margin-left: auto !important;
}

</style>