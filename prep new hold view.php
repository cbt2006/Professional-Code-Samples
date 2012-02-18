<?php $this->pageTitle=Yii::t('sd','Place Hold'); ?>

<h1><?php echo $this->pageTitle;?></h1>

<?php
	$sess=Yii::app()->session;
	if(isset($libs) && !empty($libs) && !is_string($libs[0]) && count($libs) > 0) { ?>
		<b>Choose a pickup location:</b><br />
		<form method='get' action='http://apps.facebook.com/my_bookshelves_beta/index.php/account/createHold' target='_top'>
		<table>
			<tr><th>Title</th><th>Available Locations</th></tr>
			<tr>
				<td><?php echo $title; ?></td>
				<input type='hidden' name='itemID' value='<?php echo $id; ?>' />
				<td>
					<select name='libID' id='libID'>
						<?php foreach($libs as $lib) { ?>
							<option value='<?php echo array_key_exists('id', $lib) ? $lib['id'] : 'N/A'; ?>'><?php echo array_key_exists('description', $lib) ? $lib['description'] : 'N/A'; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr><td colspan='2'>
			<label class="fb_button2 fb_button_gray">
				<input id="createHold" name="createHold" type="submit" value="<?php echo Yii::t("sd", "Place Hold"); ?>" />
			</label>
			<label class="fb_button2 fb_button_gray">
				<input id="createHold" name="cancel" type="submit" value="<?php echo Yii::t("sd", "Cancel"); ?>" />
			</label>
			</td></tr>
		</table>
		</form>
	<?php } else { ?>
		<b><?php echo $libs[0]; ?></b>
	<?php }
?>