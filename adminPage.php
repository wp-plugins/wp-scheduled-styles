<div id="scheduledStylesAdminPage" class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<h2>Scheduled Styles</h2>
<p>This plugin allows a wordpress administrator to schedule a different css file to be used on the website for holidays or special events for all visitors.</p>
<p>This plugin brought to you for free by <a href="http://www.itegritysolutions.ca/community/wordpress/scheduled-styles/" target="_blank">ITegrity Solutions</a>.</p>
<?php 

$styleSchedule = $this->read_schedule('active');

$theme = wp_get_theme();
$files = $theme->get_files();
$cssFiles = array();
foreach($files as $key => $value){
	$length = strlen('.css');
	if( (substr($key, -$length) === '.css') ) {
		$cssFiles[]=$key;
	}
}

if($_POST['submit'])
{
?>
<div id="message">New schedule saved successfully.</div>
<?php }?>

<div id='scheduledItemsHeader'>
	<div class='styleSheetCol'>Css File Name</div>
	<div class='startTimeCol'>Start Date</div>
	<div class='endTimeCol'>End Date</div>
	<div class='repeatYearlyCol'>Repeat Yearly</div>
</div>

<!-- This div contains a blank record that will be used by jQuery as a template for adding new records -->
<div id='newStyleTemplate'>
	<div class='scheduledItem' id='newStyleNUMZ'>
		<div class='styleSheetCol'>
			<select name='newStyleNUMZ-stylesheet' class='required'>
			<?php foreach($cssFiles as $cssFile){?>
				<option value="<?php echo $cssFile; ?>"><?php echo $cssFile; ?></option>
			<?php }?>
			</select>
		</div>
		<div class='startTimeCol'>
			<input name='newStyleNUMZ-startTime' type='text' size='11' class='datePicker required' maxlength="10" />
		</div>
		<div class='endTimeCol'>
			<input name='newStyleNUMZ-endTime' type='text' size='11' class='datePicker required' maxlength="10" />
		</div>
		<div class='repeatYearlyCol'>
			<input type='checkbox' name='newStyleNUMZ-repeatYearly' />
		</div>
		<div class='miscCol'>
			<input type='checkbox' name='newStyleNUMZ-delete' class='hiddenInput' />
			<input type="hidden" name='newStyleKeys[]' class='hiddenInput' value='NUMZ' />
			<a class='deleteLink' href='#'>Delete</a>
		</div>
	</div>
</div>
<form method="post" id="scheduledStylesForm">
<div id="scheduledStylesFormError"></div>
    
<div id="scheduledStyles">
	<?php
	wp_nonce_field('scheduledStylesNonceField');
	//loop through each scheduled item, displaying a record to the admin
	foreach($styleSchedule as $scheduledItem)
	{
		$id=$scheduledItem->id;
		$startTime= $scheduledItem->startTime;
		$endTime=$scheduledItem->endTime;
		$cssFileSelected=$scheduledItem->cssFile;
		$repeatedYearly= '';
		if($scheduledItem->repeatYearly ==1)
			$repeatedYearly= " checked";
			
		?>
		<div class='scheduledItem' id='scheduledItem-<?php echo $id; ?>'>
			<div class='styleSheetCol'>				
				<select name='items<?php echo $id; ?>-stylesheet' class='required'>
				<?php foreach($cssFiles as $cssFile){?>
					<option value="<?php echo $cssFile;?>" <?php if($cssFileSelected==$cssFile) echo " selected='selected'";?>><?php echo $cssFile; ?></option>
				<?php }?>
				</select>
			</div>
			
			<div class='startTimeCol'>
				<input name='items<?php echo $id; ?>-startTime' type='text' size='11' class='datePicker required' value='<?php echo $startTime; ?>' maxlength="10" />
			</div>
			<div class='endTimeCol'>
				<input name='items<?php echo $id; ?>-endTime' type='text' size='11' class='datePicker required' value='<?php echo $endTime; ?>' maxlength="10" />
			</div>
			<div class='repeatYearlyCol'>
				<input type='checkbox' name='items<?php echo $id; ?>-repeatYearly' <?php echo $repeatedYearly?> />
				<?php //TODO UI get Checkbox centered ?>
			</div>
			<div class='miscCol'>
				<input type='checkbox' name='items<?php echo $id; ?>-delete' class='hiddenInput' />
				<input type='hidden' name='itemKeys[]' class='hiddenInput' value='<?php echo $id; ?>' />
				<a class='deleteLink' href='#'>Delete</a>
			</div>
		</div><?php
	}?>
	</div>
	<div id='bottomControls'>
		<div id='addItem'><a class='addScheduledStyle' href='#'>Add New Scheduled Style</a></div>
		<div id='submitButton'>
			<input id='submit' class='button-primary' type='submit' value='Save Changes' name='submit' />
		</div>
	</div>
</form>
</div>