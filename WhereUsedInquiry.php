<?php

/* $Id$*/

include('includes/session.inc');
$title = _('Where Used Inquiry');
include('includes/header.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}

echo '<a href="' . $rootpath . '/SelectProduct.php">' . _('Back to Items') . '</a>
	<br />
	<p class="page_title_text">
		<img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $title . '
	</p>';
if (isset($StockID)){
	$result = DB_query("SELECT description,
								units,
								mbflag
						FROM stockmaster
						WHERE stockid='".$StockID."'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0){
		prnMsg(_('The item code entered') . ' - ' . $StockID . ' ' . _('is not set up as an item in the system') . '. ' . _('Re-enter a valid item code or select from the Select Item link above'),'error');
		include('includes/footer.inc');
		exit;
	}
	echo '<br />
		<div class="centre"><h3>' . $StockID . ' - ' . $myrow[0] . '  (' . _('in units of') . ' ' . $myrow[1] . ')</h3></div>';
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div class="centre">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($StockID)) {
	echo _('Enter an Item Code') . ': <input type="text" name="StockID" size="21" maxlength="20" value="' . $StockID . '" />';
} else {
	echo _('Enter an Item Code') . ': <input type="text" name="StockID" size="21" maxlength="20" />';
}

echo '<input type="submit" name="ShowWhereUsed" value="' . _('Show Where Used') . '" />';

echo '<br />
      </div>';

if (isset($StockID)) {

	$SQL = "SELECT bom.*,
				stockmaster.description
			FROM bom INNER JOIN stockmaster
			ON bom.parent = stockmaster.stockid
			WHERE component='" . $StockID . "'
			AND bom.effectiveafter<='" . Date('Y-m-d') . "'
			AND bom.effectiveto >='" . Date('Y-m-d') . "'";

	$ErrMsg = _('The parents for the selected part could not be retrieved because');;
	$result = DB_query($SQL,$db,$ErrMsg);
	if (DB_num_rows($result)==0){
		prnMsg(_('The selected item') . ' ' . $StockID . ' ' . _('is not used as a component of any other parts'),'error');
	} else {

		echo '<table width="97%" class="selection">';

		$TableHeader = '<tr>
							<th>' . _('Used By') . '</th>
							<th>' . _('Work Centre') . '</th>
							<th>' . _('Location') . '</th>
							<th>' . _('Quantity Required') . '</th>
							<th>' . _('Effective After') . '</th>
							<th>' . _('Effective To') . '</th>
						</tr>';
		echo $TableHeader;
		$k=0;
		while ($myrow=DB_fetch_array($result)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';;
				$k=1;
			}

			echo '<td><a target="_blank" href="' . $rootpath . '/BOMInquiry.php?StockID=' . $myrow['parent'] . '" alt="' . _('Show Bill Of Material') . '">' . $myrow['parent']. ' - ' . $myrow['description']. '</a></td>
				<td>' . $myrow['workcentreadded']. '</td>
				<td>' . $myrow['loccode']. '</td>
				<td class="number">' . locale_number_format($myrow['quantity'],'Variable') . '</td>
				<td>' . ConvertSQLDate($myrow['effectiveafter']) . '</td>
				<td>' . ConvertSQLDate($myrow['effectiveto']) . '</td>
                </tr>';

			//end of page full new headings if
		}

		echo '</table>';
	}
} // StockID is set

echo '<script  type="text/javascript">defaultControl(document.forms[0].StockID);</script>';

echo '</form>';
include('includes/footer.inc');
?>