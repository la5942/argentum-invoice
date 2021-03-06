<?php
	$total_income = 0;
	$total_paid = 0;
?>
<h1>View All Invoices</h1>
<p><?php for ($i = date('Y'); $i >= 2008; $i--): ?>
        <?=html::anchor('invoice/list_all/'.$i, $i, array('class' => $this->uri->segment(3, date('Y')) == $i ? 'current' : '')) ?>
<?php endfor; ?></p>
<p><?php for ($i = 1; $i <= 12; $i++): ?>
        <?=html::anchor('invoice/list_all/'.$this->uri->segment(3, date('Y')).'/'.$i, date('F', mktime(0, 0, 0, $i, 1, 1)), array('class' => $this->uri->segment(4, date('n')) == $i ? 'current' : '')) ?>
<?php endfor; ?></p>
<table class="invoice_list">
	<tbody>
		<tr>
			<th>Invoice ID</th>
			<th>Client</th>
			<th>Total Income</th>
			<th>Total Paid</th><?php if (Auth::instance()->logged_in('admin')):?>
			<td>Admin</td><?php endif;?>
		</tr>
		<?php foreach ($invoices as $invoice):?><tr<?php if ($invoice->total_income() > $invoice->total_paid()):?> class="unpaid"<?php endif;?>>
			<td><?=html::anchor('invoice/view/'.$invoice->id, $invoice->id)?></td>
			<td><?=$invoice->client->company_name?></td>
			<td>$<?=number_format($invoice->total_income(), 2)?></td>
			<td>$<?=number_format($invoice->total_paid(), 2)?></td>
			<?php
				$total_income+=$invoice->total_income();
				$total_paid+=$invoice->total_paid();
			?>
			<?php if (Auth::instance()->logged_in('admin')):?><td>
				<?=html::anchor('admin/invoice/post_payment/'.$invoice->id, html::image(array('src' => 'images/icons/money_add.png', 'alt' => 'Post Payment')))?>
				<?=html::anchor('admin/invoice/view_payments/'.$invoice->id, html::image(array('src' => 'images/icons/money.png', 'alt' => 'View Payments')))?>
			</td>
			<?php endif;?>
		<?php endforeach;?></tr>
		<tr class="total_row">
			<td colspan="2"></td>
			<td>$<?=number_format($total_income, 2)?></td>
			<td>$<?=number_format($total_paid, 2)?></td>
			<?php if (Auth::instance()->logged_in('admin')):?><td></td><?php endif;?>
		</tr>
	</tbody>
</table>