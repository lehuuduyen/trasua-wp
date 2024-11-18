<?php

/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders); ?>

<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
	<thead>
		<tr>
			<?php foreach (wc_get_account_affilate_columns() as $column_id => $column_name) : ?>
				<th style="text-align: left;" scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr($column_id); ?>"><span class="nobr"><?php echo esc_html($column_name); ?></span></th>
			<?php endforeach; ?>
		</tr>
	</thead>

	<tbody>
		<?php
		if (count($listAffilate) > 0) {
			foreach ($listAffilate as $affilate) {
				$style = 'honeydew';

				if ($affilate->status == '1') {
					$style = 'springgreen;';
				}
		?>
				<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-order" style="background-color: <?= $style ?>;">
					<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-" scope="row"><?= date('d/m/Y', strtotime($affilate->create_at)) ?></th>
					<?php
					if ($affilate->status == '1') {
					?>
					<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-" scope="row"><?=$affilate->total_order?></th>
						<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-" scope="row"><?=$affilate->commission?></th>
						<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-" scope="row"></th>
					
						<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-" style="color:green" scope="row">Nhận được</th>
					<?php
					} else {
					?>
					<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-" scope="row"></th>
						<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-" scope="row"></th>
						<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-" scope="row"><?=$affilate->commission?></th>
					
						<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-" style="color:red" scope="row">Rút tiền</th>

					<?php
					}

					?>

				</tr>
			<?php
			}
		} else {
			?>
			<tr>
				<td style="text-align: center;" colspan="4">Chưa có danh sách con mua hàng</td>
			</tr>
		<?php
		}

		?>
	</tbody>
</table>

<?php do_action('woocommerce_after_account_orders', $has_orders); ?>