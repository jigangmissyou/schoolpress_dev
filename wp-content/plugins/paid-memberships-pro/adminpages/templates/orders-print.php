<?php
/**
 * Template for Print Invoices
 *
 * @since 1.8.6
 */
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<style>
		.main, .header {
			display: block;
		}
		.right {
			display: inline-block;
			float: right;
		}
		.alignright {
			text-align: right;
		}
		.aligncenter {
			text-align: center;
		}
		.invoice, .invoice tr, .invoice th, .invoice td {
			border: 1px solid;
			border-collapse: collapse;
			padding: 4px;
		}
		.invoice {
			width: 100%;
		}
		@media screen {
			body {
				max-width: 50%;
				margin: 0 auto;
			}
		}
	</style>
</head>
<body>
	<header class="header">
		<div>
			<h2><?php bloginfo( 'sitename' ); ?></h2>
		</div>
		<div class="right">
			<table>
				<tr>
					<td><?php echo __('Invoice #: ', 'pmpro') . '&nbsp;' . $order->code; ?></td>
				</tr>
				<tr>
					<td>
						<?php echo __( 'Date:', 'pmpro' ) . '&nbsp;' . date( 'Y-m-d', $order->timestamp ) ?>
					</td>
				</tr>
			</table>
		</div>
	</header>
	<main class="main">
		<p>
			<?php echo pmpro_formatAddress(
				$order->billing->name,
				$order->billing->address1,
				$order->billing->address2,
				$order->billing->city,
				$order->billing->state,
				$order->billing->zip,
				$order->billing->country,
				$order->billing->phone
			); ?>
		</p>
		<table class="invoice">
			<tr>
				<th><?php _e('ID', 'pmpro'); ?></th>
				<th><?php _e('Item', 'pmpro'); ?></th>
				<th><?php _e('Price', 'pmpro'); ?></th>
			</tr>
			<tr>
				<td class="aligncenter"><?php echo $level->id; ?></td>
				<td><?php echo $level->name; ?></td>
				<td class="alignright"><?php echo $order->subtotal; ?></td>
			</tr>
			<tr>
				<th colspan="2" class="alignright"><?php _e('Subtotal', 'pmpro'); ?></th>
				<td class="alignright"><?php echo $order->subtotal; ?></td>
			</tr>
			<tr>
				<th colspan="2" class="alignright"><?php _e('Tax', 'pmpro'); ?></th>
				<td class="alignright"><?php echo $order->tax; ?></td>
			</tr>
			<tr>
				<th colspan="2" class="alignright"><?php _e('Total', 'pmpro'); ?></th>
				<th class="alignright"><?php echo pmpro_formatPrice( $order->total ); ?></th>
			</tr>
		</table>
	</main>
</body>
</html>