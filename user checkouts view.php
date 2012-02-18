<?php
if(!is_string($checkouts[0])) {
	if(count($checkouts) > 0) {
		?>
		<form method='get' action='http://apps.facebook.com/my_bookshelves_beta/index.php/account/renewCheckout' target='_top'>
		<label class="fb_button2 fb_button_gray">
			<input id="searchButton" name="renew" type="submit" value="<?php echo Yii::t("sd", 'Renew Checkout') ?>"/>
		</label>
		<table cellpadding="2" cellspacing="0" border="0" class="accordionTable">
		<tr>
			<th> </th>
			<th><?php echo Yii::t("sd", 'Title') ?></th>
			<th><?php echo Yii::t("sd", 'Author') ?></th>
			<th><?php echo Yii::t("sd", 'Checkout Date') ?></th>
			<th><?php echo Yii::t("sd", 'Due Date') ?></th>
			<th><?php echo Yii::t("sd", 'Overdue') ?></th>
		</tr>
		<!-- field to identify which page is being submitted so that in event of error, we know which panel to open in the accordion first -->
		<input type='hidden' name='panelName' value='checkoutsUser' />
		<?php
		foreach($checkouts as $item) {
			echo '<tr>';
			if(array_key_exists('itemID', $item)) {
				echo '<td><input type="radio" name="itemID" value="' . $item['itemID'] . '" /></td>';
			} elseif(array_key_exists('itemBarcode', $item)) { // Horizon - must renew item based on bar code, not item ID
				echo '<td><input type="radio" name="itemBarcode" value="' . $item['itemBarcode'] . '" /></td>';
			}
			echo '<td>' . (array_key_exists('title', $item) ? $item['title'] : ' ') . '</td>';
			echo '<td>' . (array_key_exists('author', $item) ? $item['author'] : ' ') . '</td>';
			if(array_key_exists('checkoutDate', $item)) {
				echo '<td>' . Yii::app()->dateFormatter->formatDateTime((string)$item['checkoutDate'],'short',null) : Yii::t("sd", 'N/A')) . '</td>';
			} elseif(array_key_exists('ckoDate', $item) {
				echo '<td>' . Yii::app()->dateFormatter->formatDateTime((string)$item['ckoDate'],'short',null) : Yii::t("sd", 'N/A')) . '</td>';
			}
			echo '<td>' . (array_key_exists('dueDate', $item) ? Yii::app()->dateFormatter->formatDateTime((string)$item['dueDate'],'short',null) : Yii::t("sd", 'N/A')) . '</td>';
			echo '<td>' . (array_key_exists('overdue', $item) ? ($item['overdue'] == 'true' ? Yii::t("sd", 'Yes') : Yii::t("sd", 'No') ) : Yii::t("sd", Yii::t("sd", 'N/A'))) . '</td>';
			?>
		</tr>
	<?php } ?>
		</table>
		<?php if(count($checkouts) > 10) { ?>
			<label class="fb_button2 fb_button_gray">
				<input id="searchButton" name="renew" type="submit" value="<?php echo Yii::t("sd", 'Renew Checkout') ?>"/>
			</label>
		<?php } ?>
		</form>
		<br />
		<b><?php echo Yii::t("sd", 'Total Items Checked Out') ?>: <?php echo count($checkouts); ?></b><br />
		<b><?php echo Yii::t("sd", 'Total Items Overdue') ?>: 
		<?php 
			$overdue = 0;
			foreach($checkouts as $item) {
				if(array_key_exists('overdue', $item)) {
					if($item['overdue'] == 'true') $overdue++;
				}
			}
			echo $overdue;
		?>
		</b>
	<?php
	} else {
		echo '<table>';
		echo "<tr><td colspan='6'><b>" . Yii::t("sd", 'No checkouts found.') . "</b></td></tr>";
		echo '</table>';
	}
} else { ?>
	<b><?php echo $checkouts[0]; ?></b>
<?php } ?>