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


$document = JFactory::getDocument();
$jsFile = JURI::base(true).'/components/com_creativecontactform/assets/js/creativelib.js';
$document->addScript($jsFile);

if(true) {

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
$saveOrder	= $listOrder == 'sp.ordering';

$joomla4 = version_compare(JVERSION, '4', '>=') ? true : false;

if($joomla4) { // Bootstrap 4
	$classes_row = 'row';
	$classes_col = 'col-md-';
} else { // Boostrap 2.3.2
	$classes_row = 'row-fluid';
	$classes_col = 'span';
}

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_creativecontactform&task=submissions.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'desc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
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
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH_BY_NAME_EMAIL');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH_BY_NAME_EMAIL'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CREATIVECONTACTFORM_SEARCH_BY_NAME_EMAIL'); ?>" <?php if($joomla4) echo 'style="height:46px;"'?> />
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
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>

	<div style="overflow: hidden">
		<div style="color: rgb(235, 9, 9);font-size: 16px;font-weight: bold;padding: 12px 0 0 12px;">Please Upgrade to PRO Version to use Submissions!</div>
		<div id="cpanel" style="float: left;">
			<div class="icon" style="float: right;">
				<a href="<?php echo JText::_( 'COM_CREATIVECONTACTFORM_SUBMENU_BUY_PRO_VERSION_LINK' ); ?>" target="_blank" title="<?php echo JText::_( 'COM_CREATIVECONTACTFORM_SUBMENU_BUY_PRO_VERSION_DESCRIPTION' ); ?>">
					<table style="width: 100%;height: 100%;text-decoration: none;">
						<tr>
							<td align="center" valign="middle">
								<img src="components/com_creativecontactform/assets/images/shopping_cart.png" /><br />
								<?php echo JText::_( 'COM_CREATIVECONTACTFORM_SUBMENU_BUY_PRO_VERSION' ); ?>
							</td>
						</tr>
					</table>
				</a>
			</div>
		</div>
	</div>
	<div style="color: rgb(0, 85, 182);font-size: 25px;text-align: center;clear: both;margin-bottom: 5px;">Submissions Demo</div>


		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					
					<th width="1%" class="hidden-phone center">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="1%"></th>
					<th width="1%"></th>
					<th width="20%">
						<?php echo JText::_('COM_CREATIVECONTACTFORM_TIME');?>
					</th>
					<th width="20%">
						<?php echo JText::_('COM_CREATIVECONTACTFORM_NAME');?>
					</th>
					<th width="20%">
						<?php echo JText::_('COM_CREATIVECONTACTFORM_EMAIL');?>
					</th>
					<th width="">
						<?php echo JText::_('COM_CREATIVECONTACTFORM_FORM'); ?>
					</th>
					<th width="">
						<?php echo JText::_('COM_CREATIVECONTACTFORM_IP'); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'sp.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$n = count($this->items);
			foreach ($this->items as $i => $item) :
				$ordering	= $listOrder == 'sp.ordering';
				$unread_class = $item->viewed == 0 ? 'cs_unread' : '';
				?>
				<tr class="<?php echo $unread_class;?> cs_row<?php echo $i % 2; ?> cs_sub_item" sub_id="<?php echo $item->id; ?>">
					<td class="cg_disabled center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="cg_disabled" style="padding: 4px 0px !important;"><div sub_id="<?php echo $item->id; ?>" class="icon_star icon_star_<?php echo $item->star_index; ?> cg_disabled" star_index="<?php echo $item->star_index; ?>"></div></td>
					<td class="cg_disabled" style="padding: 4px 2px 4px 2px !important;"><div sub_id="<?php echo $item->id; ?>"  class="icon_imp icon_imp_<?php echo $item->imp_index; ?> cg_disabled" imp_index="<?php echo $item->imp_index; ?>"></div></td>
					<td align="small hidden-phone">
							<?php echo date('j F, Y | H:i:s', strtotime($item->date)); ?>
					</td>
					<td align="small hidden-phone">
							<?php echo $item->name; ?>
					</td>
					<td align="small hidden-phone">
							<?php echo $item->email; ?>
					</td>
					<td align="small hidden-phone">
							<?php echo $item->form_name; ?>
					</td>
					<td align="small hidden-phone">
							<?php echo $item->ip; ?>
					</td>
					<td align="center hidden-phone">
						<?php echo $item->id; ?>
					</td>
				</tr>
				<tr class="">
					<td colspan="9" class="cs_submissions_tr" style="padding: 0;">
						<div class="cs_submission_main_wrapper">
							<div class="cs_submission_wrapper">
								<div class="cs_sub_heading"><?php echo JText::_('COM_CREATIVECONTACTFORM_SUBMISSION_BODY'); ?></div>
								<div class="cs_sub_inner">
									<?php echo trim(nl2br($item->message),'<br>'); ?>
								</div>
								<div class="cs_sub_heading"><?php echo JText::_('COM_CREATIVECONTACTFORM_SUBMISSION_USER_DATA'); ?></div>
								<div class="cs_sub_inner">
									<?php
										$sub_user_data = '';
										$sub_user_data .= 'Page Title: '.$item->page_title."<br />";
										$sub_user_data .= 'Page Url: <a href="'.$item->page_url.'" target="_blank">'.$item->page_url."</a><br />";
										$sub_user_data .= 'IP Address: '.$item->ip."<br />";	
										$sub_user_data .= 'Browser: '.$item->browser."<br />";	
										$sub_user_data .= 'Operating System: '.$item->op_s."<br />";	
										$sub_user_data .= 'Screen Resolution: '.$item->sc_res."<br />";
										$sub_user_data .= 'Submission ID: '.$item->id."<br />";

										echo $sub_user_data;
									?>
								</div>
								<?php
									if($item->uploads != '') {
										echo '<div class="cs_sub_heading">'.JText::_('COM_CREATIVECONTACTFORM_SUBMISSION_UPLOADS').'</div>';
										$sub_uploads = explode('~', $item->uploads);
										echo '<div class="cs_sub_inner">';
										foreach($sub_uploads as $k => $sub_upload) {
											$q = $k + 1;
											$filename = explode('creativeupload/files/', $sub_upload);
											$filename = $filename[1];
											$sub_upload_path = JURI::base() . '../';
											echo $q.'. <a href="'.$sub_upload_path.$sub_upload.'" target="_blank">'.$filename.'</a><br />';
										}
										echo '</div>';
									}
								?>
							</div>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="9">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
		</table>
		<input type="hidden" name="view" value="submissions" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?php include (JPATH_BASE.'/components/com_creativecontactform/helpers/footer.php'); ?>

<style>
.cs_submission_wrapper {
	box-shadow: inset 0 0 2px 1px #E6E6E6;
    border: 1px solid #CACACA;
    padding: 10px;
    /*border-radius: 5px;*/
    background-color: #EAEAEA;
}
.cs_sub_heading {
	text-shadow: 1px 1px 2px #2B2B2B;
    box-shadow: inset 0 0 2px 1px #565656;
    background-color: #6D6D6D;
    border: 1px solid #464646;
    font-size: 14px;
    text-align: left;
    font-weight: bold;
    padding: 5px 13px;
    color: white;
}
.cs_sub_inner {
	margin: 15px;
	color: #333;
}
.cs_unread td {
	font-weight: bold;
}
.cs_row0, .cs_row1 {
	cursor: pointer;
}
.cs_row0 td, .cs_row1 td{
	padding: 5px 8px !important;
}
.cs_row0 td {
	background-color: #EFF9EF   !important;
} 
.cs_row1 td {
	background-color: #EFF9EF   !important;
}
tr.cs_row0:hover td, tr.cs_row1:hover td {
	background-color: #FFFFE5  !important;
}
.cs_unread.cs_row0 td{
	background-color: #F9F9F9 !important;
}
.cs_unread.cs_row1 td{
	background-color: #F3F3F3 !important;
}


.icon_star {
	width: 19px;
	height: 19px;
	margin: 1px 1px;
}
.icon_star_0 {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/star0.png") left top no-repeat;
}
.icon_star_1 {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/star1.png") left top no-repeat;
}
.icon_star_2 {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/star2.png") left top no-repeat;
}
.icon_star_3 {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/star3.png") left top no-repeat;
}
.icon_star_4 {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/star4.png") left top no-repeat;
}
.icon_star_5 {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/star5.png") left top no-repeat;
}
.icon_star_6 {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/star6.png") left top no-repeat;
}

.icon_imp {
	width: 19px;
	height: 19px;
	margin: 1px 1px;
}
.icon_imp_0 {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/important_icon.png") 0px 0px no-repeat;
}
.icon_imp_0:hover {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/important_icon.png") 0px -38px no-repeat;
}
.icon_imp_1 {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/important_icon.png") -38px -19px no-repeat;
}
.icon_imp_1:hover {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/important_icon.png") -38px -38px no-repeat;
}
.icon_imp_2 {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/important_icon.png") -19px -19px no-repeat;
}
.icon_imp_2:hover {
	background: url("<?php echo JURI::base(true);?>/components/com_creativecontactform/assets/images/sub_icons/important_icon.png") -19px -38px no-repeat;
}



.cs_submission_main_wrapper {
	height: 0px;
	overflow: hidden;
}
</style>
<script>
(function($) {
	$(document).ready(function() {
		$('.cs_sub_item').click(function(e) {

			var senderElement = $(e.target);
			if(senderElement.hasClass('cg_disabled') || senderElement.attr('type') == "checkbox") {
				return;
			}
			// remove unread class
			$(this).removeClass('cs_unread');

			var $cs_submission_main_wrapper = $(this).next('tr').find('.cs_submission_main_wrapper');
			
			if($(this).hasClass('cs_opened')) {
				$(this).removeClass('cs_opened');
				$cs_submission_main_wrapper.animate({
					height: 0
				},400);
			}
			else {
				// remove opened class
				$('.cs_submission_main_wrapper').not($cs_submission_main_wrapper).parents('tr').prev('tr').removeClass('cs_opened');
				// close all opened
				$('.cs_submission_main_wrapper').not($cs_submission_main_wrapper).animate({
					height: 0
				},400);

				// opene submission
				$(this).addClass('cs_opened');
				var h = $cs_submission_main_wrapper.find('.cs_submission_wrapper').height() + 30*1;
				$cs_submission_main_wrapper.animate({
					height: h
				},400);	
			}

			// set viewed
			var sub_id = $(this).attr("sub_id");
			$.ajax({

					type : "post",
					url: "index.php?option=com_creativecontactform&view=creativeajax",
					data: {sub_id: sub_id, viewed_index: 1, type: 'set_viewed'}, 
					
				});

		});

			
		$(".icon_star").on('click', function() {
			$this = $(this);
			star_index = parseInt($this.attr("star_index"));
			star_index = star_index == 6 ? 0 : star_index*1 + 1*1;
			star_title=star_index == 0 ? "No Star" : "Starred";
			$this.attr("class","cg_disabled icon_star icon_star_" + star_index);
			$this.attr("title",star_title);
			$this.attr("star_index", star_index);

			star_index = parseInt(star_index);

			var sub_id = $this.attr("sub_id");

			$.ajax({

					type : "post",
					url: "index.php?option=com_creativecontactform&view=creativeajax",
					data: {sub_id: sub_id, star_index: star_index, type: 'update_star'}, 
					
				});

		});
		$(".icon_imp").on('click', function() {
			$this = $(this);
			imp_index = parseInt($this.attr("imp_index"));
			imp_index = imp_index == 2 ? 0 : imp_index*1 + 1*1;
			imp_title=imp_index == 0 ? "Not Important" : "Important";
			$this.attr("class","cg_disabled icon_imp icon_imp_" + imp_index);
			$this.attr("title",imp_title);
			$this.attr("imp_index", imp_index);

			imp_index = parseInt(imp_index);

			var sub_id = $this.attr("sub_id");

			$.ajax({

				type : "post",
				url: "index.php?option=com_creativecontactform&view=creativeajax",
				data: {sub_id: sub_id, imp_index: imp_index, type: 'update_imp'}, 
				
			});
		});

		// $("#toolbar-delete").find('button').mouseup(function(e) {
		// 	alert('click');
		// 	e.stopPropagation();
		// 	return false;
		// })



	});
})(creativeJ);
</script>

<?php }?>