<?php

if (!class_exists("NTCO_Edit_Order_style")) {
	class NTCO_Edit_Order_style {
		private $stt = 1;
		public function __construct() {
			if (vn_checkout()->hpos_enabled()) {
				add_filter("manage_woocommerce_page_wc-orders_columns", [$this, "ntco_shop_order_columns"], 20);
				add_action("manage_woocommerce_page_wc-orders_custom_column", [$this, "ntco_render_shop_order_columns"], 20, 2);
			} else {
				add_filter("manage_shop_order_posts_columns", [$this, "ntco_shop_order_columns"], 20);
				add_action("manage_shop_order_posts_custom_column", [$this, "ntco_render_shop_order_columns"], 20, 2);
			}
			add_filter("woocommerce_admin_order_date_format", [$this, "ntco_woocommerce_admin_order_date_format"]);
			add_action("admin_head", [$this, "ntco_order_style"]);
			add_filter("post_row_actions", [$this, "ntco_page_row_actions"], 999, 2);
			add_action("wp_ajax_wc_ntco_change_order_status", [$this, "wc_ntco_change_order_status"]);
		}
		public function ntco_page_row_actions($actions, $post) {
			if ("shop_order" == $post->post_type) {
				return [];
			}
			return $actions;
		}
		public function ntco_shop_order_columns($posts_columns) {
			unset($posts_columns["order_status"]);
			unset($posts_columns["order_title"]);
			unset($posts_columns["billing_address"]);
			unset($posts_columns["shipping_address"]);
			unset($posts_columns["customer_message"]);
			unset($posts_columns["order_notes"]);
			unset($posts_columns["order_date"]);
			unset($posts_columns["order_actions"]);
			unset($posts_columns["order_number"]);
			$posts_columns = $this->ntco_array_insert_after("cb", $posts_columns, "ntco_order_title", "Thông tin");
			$posts_columns = $this->ntco_array_insert_after("ntco_order_title", $posts_columns, "ntco_products", "Sản phẩm");
			$posts_columns = $this->ntco_array_insert_after("order_total", $posts_columns, "order_date", "Ngày đặt hàng");
			$posts_columns = $this->ntco_array_insert_after("order_date", $posts_columns, "ntco_order_status", "Trạng thái");
			$posts_columns = apply_filters("ntco_ghtk_manage_shop_order_posts_columns", $posts_columns, $this);
			return $posts_columns;
		}
		public function ntco_array_insert_before($key, &$array, $new_key, $new_value) {
			if (array_key_exists($key, $array)) {
				$new = [];
				foreach ($array as $k => $value) {
					if ($k === $key) {
						$new[$new_key] = $new_value;
					}
					$new[$k] = $value;
				}
				return $new;
			} else {
				return $array;
			}
		}
		public function ntco_array_insert_after($key, &$array, $new_key, $new_value) {
			if (array_key_exists($key, $array)) {
				$new = [];
				foreach ($array as $k => $value) {
					$new[$k] = $value;
					if ($k === $key) {
						$new[$new_key] = $new_value;
					}
				}
				return $new;
			} else {
				return $array;
			}
		}
		public function ntco_render_shop_order_columns($column, $the_order) {
			global $wp_query;
			if (is_numeric($the_order) || empty($the_order)) {
				$the_order = wc_get_order($the_order);
			}

			switch ($column) {
				case 'ntco_order_title':
					$gender = $the_order->get_meta('_billing_gender', true);
					$gender = $gender ? ($gender == 'male' ? 'Anh' : 'Chị') : '';

					if ($the_order->get_customer_id()) {
						$user = get_user_by('id', $the_order->get_customer_id());
						$username = '<a href=' . esc_url('user-edit.php?user_id=' . absint($the_order->get_customer_id())) . '>';
						$username .= esc_html(ucwords($user->display_name));
						$username .= '</a><br/>';
						$username .= $gender . ' ' . trim(sprintf(_x('%1$s %2$s', 'full name', 'ntco-vn-checkout'), $the_order->get_billing_first_name(), $the_order->get_billing_last_name()));
					} else {
						if ($the_order->get_billing_first_name() || $the_order->get_billing_last_name()) {
							$username = $gender . ' ' . trim(sprintf(_x('%1$s %2$s', 'full name', 'ntco-vn-checkout'), $the_order->get_billing_first_name(), $the_order->get_billing_last_name()));
						} else {
							$username = $the_order->get_billing_company() ? trim($the_order->get_billing_company()) : __('Guest', 'ntco-vn-checkout');
						}
					}

					printf(
						__('%1$s <br> %2$s<br>', 'ntco-vn-checkout'),
						'ID: <a href=' . esc_url(admin_url('post.php?post=' . absint($the_order->get_id()) . '&action=edit')) . ' class="row-title"><strong>#' . esc_attr($the_order->get_order_number()) . '</strong></a>',
						$username
					);

					$shipping_phone = $the_order->get_shipping_phone();
					if (!wc_ship_to_billing_address_only() && $the_order->needs_shipping_address() && $shipping_phone) {
						echo esc_html($shipping_phone) . '<br>';
					} else {
						if ($the_order->get_billing_phone()) {
							echo esc_html($the_order->get_billing_phone()) . '<br>';
						}
					}

					add_filter('woocommerce_order_formatted_shipping_address', [$this, 'ntco_woocommerce_formatted_address_replacements'], 10);
					add_filter('woocommerce_order_formatted_billing_address', [$this, 'ntco_woocommerce_formatted_address_replacements'], 10);

					$address = !wc_ship_to_billing_address_only() && $the_order->needs_shipping_address() ? $the_order->get_formatted_shipping_address() : $the_order->get_formatted_billing_address();
					if ($address) {
						echo esc_html(preg_replace('#<br\s*/?>#i', ', ', $address)) . '<br>';
					}

					remove_filter('woocommerce_order_formatted_billing_address', [$this, 'ntco_woocommerce_formatted_address_replacements'], 10);
					remove_filter('woocommerce_order_formatted_shipping_address', [$this, 'ntco_woocommerce_formatted_address_replacements'], 10);

					if ($the_order->get_billing_email()) {
						echo esc_html($the_order->get_billing_email()) . '<br>';
					}

					if ($the_order->get_shipping_method()) {
						echo '<small class="meta">' . __('Via', 'ntco-vn-checkout') . ' ' . esc_html($the_order->get_shipping_method()) . '</small>';
					}

					echo '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __('Show more details', 'ntco-vn-checkout') . '</span></button>';
					do_action('ntco_vn_checkout_order_action', $the_order);
					break;

				case 'ntco_details':
					if ($the_order->get_customer_note()) {
						echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip($the_order->get_customer_note()) . '">1 ghi chú</span>';
					} else {
						echo '<span class="na">0 ghi chú</span>';
					}
					echo '<br>';
					echo '<a href=' . esc_url(admin_url('post.php?post=' . absint($the_order->get_id()) . '&action=edit')) . '>Chi tiết</a>';
					break;

				case 'ntco_total':
					echo $the_order->get_formatted_order_total();
					break;

				case 'ntco_products':
					ob_start();
					$all_prods = vn_checkout()->get_product_args($the_order);
					if ($all_prods && !is_wp_error($all_prods) && !empty($all_prods)) {
						echo '<p>Có ' . count($all_prods) . ' sản phẩm</p>';
						echo '<div class="sp_in_order_loop_wrap"><table class="ntco_table_style"><tbody>';
						foreach ($all_prods as $product) {
							echo '<tr><td style="width: 50px;">' . $product['thumb'] . '</td><td>' . $product['name'] . '</td><td style="width: 45px;"><strong>SL:</strong> ' . $product['quantity'] . '</td></tr>';
						}
						echo '</tbody></table></div><hr>';
					}
					$customer_note = $the_order->get_customer_note();
					if ($customer_note) {
						echo '<strong>Ghi chú:</strong> ' . $customer_note;
					}
					do_action('vn_checkout_after_product_column_order', $the_order);
					echo apply_filters('ghtk_order_column_ntco_products', ob_get_clean(), $all_prods, $the_order);
					break;

				case 'ntco_stt':
					ob_start();
					$paged = get_query_var('paged') ? get_query_var('paged') : 1;
					$per_page = $wp_query->query_vars['posts_per_page'];
					echo $this->stt++ + $per_page * ($paged - 1);
					echo apply_filters('ghtk_order_column_ntco_stt', ob_get_clean(), $the_order);
					break;

				case 'ntco_order_status':
					if ($the_order->get_customer_note()) {
						echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip($the_order->get_customer_note()) . '">1 ghi chú</span>';
					} else {
						echo '<span class="na">0 ghi chú</span>';
					}
					echo '<br><hr>';

					echo '<select id="ntco_order_status" class="wc-enhanced-select">';
					$statuses = wc_get_order_statuses();
					foreach ($statuses as $status => $status_name) {
						echo '<option data-name="' . esc_attr(str_replace('wc-', '', $status)) . '" value="' . esc_attr($status) . '" ' . selected($status, 'wc-' . $the_order->get_status('edit'), false) . '>' . esc_html($status_name) . '</option>';
					}
					echo '</select>';

					echo '<p style="margin-bottom: 10px;">
							<a class="button tips change_status" href="javascript:void(0)" data-tip="' . esc_attr__('Lưu', 'ntco-vn-checkout') . '" data-href="' . esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=wc_ntco_change_order_status&order_id=' . $the_order->get_id()), 'ntco-woocommerce-mark-order-status')) . '">
								' . esc_html__('Lưu', 'ntco-vn-checkout') . '
							</a>
							<button class="button tips delete_order" data-href="' . esc_url(get_delete_post_link($the_order->get_id(), '', true)) . '" data-tip="' . esc_attr__('Xóa', 'ntco-vn-checkout') . '">
								' . esc_html__('Xóa', 'ntco-vn-checkout') . '
							</button>
						</p><hr>';

					echo '<a href="' . esc_url(admin_url('post.php?post=' . absint($the_order->get_id()) . '&action=edit')) . '">' . esc_html__('Xem chi tiết', 'ntco-vn-checkout') . '</a>';
					break;


				case 'ntco_actions':
					echo '<p>
							<a class="button tips change_status" href="javascript:void(0)" data-tip="' . esc_attr__('Lưu', 'ntco-vn-checkout') . '" data-href="' . esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=wc_ntco_change_order_status&order_id=' . $the_order->get_id()), 'ntco-woocommerce-mark-order-status')) . '">
								' . esc_html__('Lưu', 'ntco-vn-checkout') . '
							</a>
							<button class="button tips delete_order" href="' . esc_url(get_delete_post_link($the_order->get_id(), '', true)) . '" data-tip="' . esc_attr__('Xóa', 'ntco-vn-checkout') . '">
								' . esc_html__('Xóa', 'ntco-vn-checkout') . '
							</button>
						</p>';
					break;

				case 'ntco_notes':
					$latest_notes = wc_get_order_notes(['order_id' => $the_order->get_id(), 'orderby' => 'date_created_gmt', 'type' => 'customer']);
					$latest_note = current($latest_notes);
					$count_note = count($latest_notes);
					if (isset($latest_note->content)) {
						echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip($latest_note->content) . '">' . sprintf('%d tin nhắn', $count_note) . '</span>';
					} else {
						echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip(sprintf(_n('%d tin nhắn', '%d tin nhắn', $count_note, 'ntco-vn-checkout'), $count_note)) . '">' . sprintf('%d tin nhắn', $count_note) . '</span>';
					}
					break;

				case 'ntco_message':
					if ($the_order->get_customer_note()) {
						echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip($the_order->get_customer_note()) . '">1 ghi chú</span>';
					} else {
						echo '<span class="na">0 ghi chú</span>';
					}
					break;

				default:
					return $column;
			}
		}

		public function ntco_woocommerce_admin_order_date_format() {
			return "h:i d/m/Y";
		}
		public function ntco_woocommerce_formatted_address_replacements($address) {
			unset($address["first_name"]);
			unset($address["phone"]);
			unset($address["last_name"]);
			return $address;
		}
		public function ntco_order_style() {
			$current_screen = get_current_screen();
			if (
				(isset($current_screen->base) && $current_screen->base == 'woocommerce_page_wc-orders') ||
				(isset($current_screen->post_type) && $current_screen->post_type == 'shop_order' && $current_screen->base == 'edit')
			) {
				echo '<style>
            .post-type-shop_order .wp-list-table td, .post-type-shop_order .wp-list-table th,
            .woocommerce_page_wc-orders .wp-list-table td, .woocommerce_page_wc-orders .wp-list-table th {
                width: inherit;
            }
            .post-type-shop_order .wp-list-table tbody td, .post-type-shop_order .wp-list-table tbody th,
            .woocommerce_page_wc-orders .wp-list-table tbody td, .woocommerce_page_wc-orders .wp-list-table tbody th {
                padding: 5px;
                line-height: 18px;
                border: 1px solid #e5e5e5;
            }
            .post-type-shop_order .wp-list-table .check-column,
            .woocommerce_page_wc-orders .wp-list-table .check-column {
                padding: 3px !important;
                width: 23px;
                text-align: center;
            }
            .post-type-shop_order .wp-list-table .check-column input[type="checkbox"],
            .woocommerce_page_wc-orders .wp-list-table .check-column input[type="checkbox"] {
                margin: 0;
            }
            table.wp-list-table .column-ntco_order_status span.select2 {
                margin-bottom: 10px;
            }
            .widefat .type-shop_order td {
                vertical-align: middle;
            }
            table.wp-list-table .column-customer_message, table.wp-list-table .column-ntco_message {
                width: 60px;
                padding: 5px !important;
                text-align: center;
            }
            table.wp-list-table .column-order_date {
                width: 145px;
            }
            table.wp-list-table .column-wc_actions {
                width: 145px;
            }
            table.wp-list-table .column-ntco_order_status {
                width: 155px;
                padding: 5px !important;
                text-align: center;
            }
            .widefat .column-ntco_actions a.button {
                float: left;
                margin: 0 4px 2px 0;
                cursor: pointer;
                padding: 3px 4px;
                height: auto;
            }
            .ntco_actions .button {
                display: block;
                text-indent: -9999px;
                position: relative;
                height: 1em;
                width: 1em;
                padding: 0 !important;
                height: 2em !important;
                width: 2em;
            }
            .ntco_actions .change_status::after,
            .ntco_actions .delete_order::after {
                font-family: "dashicons";
                speak: none;
                font-weight: 400;
                text-transform: none;
                -webkit-font-smoothing: antialiased;
                text-indent: 0;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                text-align: center;
                content: "\f316";
                line-height: 1.85;
                font-variant: normal;
                margin: 0;
            }
            .ntco_actions .delete_order::after {
                content: "\f182";
            }
            table.wp-list-table .column-ntco_actions,
            table.wp-list-table .column-ntco_details {
                width: 65px;
                padding: 5px !important;
                text-align: center;
            }
            .post-type-shop_order table.wp-list-table.widefat,
            .woocommerce_page_wc-orders table.wp-list-table.widefat {
                border-collapse: collapse;
            }
            .post-type-shop_order table.wp-list-table.widefat .type-shop_order td,
            .post-type-shop_order table.wp-list-table.widefat th,
            .woocommerce_page_wc-orders table.wp-list-table.widefat .type-shop_order td,
            .woocommerce_page_wc-orders table.wp-list-table.widefat th {
                vertical-align: middle;
                border: 1px solid #e5e5e5 !important;
            }
            table.wp-list-table .column-ntco_stt {
                width: 25px;
                text-align: center;
                padding-left: 2px;
                padding-right: 2px;
            }
            .post-type-shop_order .wp-list-table .column-order_total,
            .woocommerce_page_wc-orders .wp-list-table .column-order_total {
                width: 105px;
                padding: 5px !important;
                text-align: center;
            }
            .post-type-shop_order .wp-list-table .column-order_date, .post-type-shop_order .wp-list-table .column-order_status,
            .woocommerce_page_wc-orders .wp-list-table .column-order_date, .woocommerce_page_wc-orders .wp-list-table .column-order_status {
                width: 127px !important;
                text-align: center;
                padding: 5px !important;
            }
            .tablenav .alignleft.actions.bulkactions select {
                max-width: 100px;
            }
            @media (max-width: 782px) {
                body.post-type-shop_order .wp-list-table .column-order_date,
                body.woocommerce_page_wc-orders .wp-list-table .column-order_date {
                    display: none !important;
                }
                body.post-type-shop_order .wp-list-table .check-column,
                body.woocommerce_page_wc-orders .wp-list-table .check-column {
                    padding: 12px !important;
                }
                body.post-type-shop_order .wp-list-table .check-column input[type="checkbox"],
                body.woocommerce_page_wc-orders .wp-list-table .check-column input[type="checkbox"] {
                    position: relative;
                    left: -5px;
                }
            }
        </style>
        <script type="text/javascript">
            (function($){
                $(document).ready(function(){
                    $(".change_status").click(function(){
                        var thisTr = $(this).closest("tr");
                        var statusThis = $("#ntco_order_status option:selected", thisTr).data("name");
                        var thisURL = $(this).data("href");
                        var url = thisURL + "&status=" + statusThis;
                        $.post(url, {}, function(respon, status){
                            alert(respon.data);
                        });
                        return false;
                    });

                    $(".delete_order").on("click", function () {
                        var result = confirm("Bạn có chắc chắn muốn xóa vĩnh viễn order này?");
                        if (result) {
                            var thisHref = $(this).data("href");
                            window.location.href = thisHref;
                        }
                        return false;
                    });
                });
            })(jQuery)
        </script>';
			}
		}

		public function wc_ntco_change_order_status() {
			if (current_user_can("edit_shop_orders")) {
				$status = sanitize_text_field($_GET["status"]);
				$order = wc_get_order(absint($_GET["order_id"]));
				if (wc_is_order_status("wc-" . $status) && $order) {
					wc()->payment_gateways();
					$order->update_status($status, "", true);
					do_action("woocommerce_order_edit_status", $order->get_id(), $status);
					if (apply_filters("ntco_ghtk_debug", false)) {
						ob_start();
						echo "Update status qua ghtk hàm wc_ntco_change_order_status";
						$log_con = ob_get_clean();
						$log = "User: " . $_SERVER["REMOTE_ADDR"] . " - " . date("F j, Y, g:i a") . PHP_EOL . $log_con . PHP_EOL . "-------------------------" . PHP_EOL;
						file_put_contents(dirname(__FILE__) . "/log_ghtk.txt", $log, FILE_APPEND);
					}
					wp_send_json_success("Cập nhật trạng thái thành công!");
				}
			}
			wp_send_json_error("Có lỗi xảy ra");
			exit;
		}
	}
	new NTCO_Edit_Order_style();
}
if (!class_exists("ntcoDateRange")) {
	class ntcoDateRange {
		private $post_type_allow = ["shop_order"];
		public function __construct() {
			if (vn_checkout()->get_options("active_orderstyle")) {
				if (vn_checkout()->hpos_enabled()) {
					add_action("woocommerce_order_list_table_restrict_manage_orders", [$this, "form_hpos"], 10, 2);
					add_filter("woocommerce_shop_order_list_table_prepare_items_query_args", [$this, "filterquery_hpos"]);
				} else {
					add_filter("months_dropdown_results", [$this, "ntco_remove_month_filter"], 10, 2);
					add_action("restrict_manage_posts", [$this, "form"]);
					add_action("pre_get_posts", [$this, "filterquery"]);
				}
			}
		}
		public function ntco_remove_month_filter($months, $post_type) {
			if (in_array($post_type, $this->post_type_allow)) {
				return [];
			}
			return $months;
		}
		public function form_html() {
			$from = isset($_GET['ntcoDateFrom']) && $_GET['ntcoDateFrom'] ? $_GET['ntcoDateFrom'] : '';
			$to = isset($_GET['ntcoDateTo']) && $_GET['ntcoDateTo'] ? $_GET['ntcoDateTo'] : '';
			$ntcobillingState = isset($_GET['ntcobillingState']) && $_GET['ntcobillingState'] ? $_GET['ntcobillingState'] : '';
			$ntcobillingCity = isset($_GET['ntcobillingCity']) && $_GET['ntcobillingCity'] ? $_GET['ntcobillingCity'] : '';

			$country = new WC_Countries();
			$vn_state = $country->get_states('VN');

?>
<style>
	input[name="ntcoDateFrom"], input[name="ntcoDateTo"] {
		line-height: 28px;
		height: 28px;
		margin: 0;
		width: 125px;
	}
	.post-type-shop_order .tablenav select#ntcobillingState + span.select2-container {
		max-width: 155px !important;
	}
</style>

<input type="text" name="ntcoDateFrom" placeholder="Từ ngày" value="<?php echo esc_attr($from); ?>"/>
<input type="text" name="ntcoDateTo" placeholder="Đến ngày" value="<?php echo esc_attr($to); ?>"/>

<?php if ($vn_state && is_array($vn_state)) : ?>
<select name="ntcobillingState" id="ntcobillingState">
	<option value="">Lọc theo tỉnh thành</option>
	<?php foreach ($vn_state as $k => $v) : ?>
	<option value="<?php echo esc_attr($k); ?>" <?php selected($k, $ntcobillingState); ?>>
		<?php echo esc_html($v); ?>
	</option>
	<?php endforeach; ?>
</select>

<select name="ntcobillingCity" id="ntcobillingCity">
	<option value="">Lọc theo quận/huyện</option>
	<?php if ($ntcobillingState) :
			$cities = vn_checkout()->get_list_district_select($ntcobillingState);
			if ($cities) {
				foreach ($cities as $k => $name) : ?>
	<option value="<?php echo esc_attr($k); ?>" <?php selected($k, $ntcobillingCity); ?>>
		<?php echo esc_html($name); ?>
	</option>
	<?php endforeach;
			}
			endif; ?>
</select>
<?php endif; ?>

<script>
	jQuery(function ($) {
		var from = $('input[name="ntcoDateFrom"]'),
			to = $('input[name="ntcoDateTo"]');

		$('input[name="ntcoDateFrom"], input[name="ntcoDateTo"]').datepicker({dateFormat: "yy-mm-dd"});
		from.on('change', function () {
			to.datepicker('option', 'minDate', from.val());
		});
		to.on('change', function () {
			from.datepicker('option', 'maxDate', to.val());
		});
	});
</script>
<?php
		}

		public function form_hpos($order_type, $which) {
			if ($order_type != "shop_order") {
				return NULL;
			}
			if ("top" === $which) {
				$this->form_html();
			}
		}
		public function form() {
			global $typenow;
			if (in_array($typenow, $this->post_type_allow)) {
				$this->form_html();
			}
		}
		public function filterquery_hpos($order_query_args) {
			$date_after = isset($_GET["ntcoDateFrom"]) ? sanitize_text_field($_GET["ntcoDateFrom"]) : "";
			$date_before = isset($_GET["ntcoDateTo"]) ? sanitize_text_field($_GET["ntcoDateTo"]) : "";
			if (!$date_before && $date_after) {
				$date_after = date_i18n("Y-m-d", strtotime($date_after . " - 1 day"));
			}
			if (!$date_after && $date_before) {
				$date_before = date_i18n("Y-m-d", strtotime($date_before . " + 1 day"));
			}
			if ($date_before) {
				$order_query_args["date_before"] = $date_before;
			}
			if ($date_after) {
				$order_query_args["date_after"] = $date_after;
			}
			if (isset($_GET["ntcobillingState"]) && $_GET["ntcobillingState"]) {
				$order_query_args["billing_state"] = sanitize_text_field($_GET["ntcobillingState"]);
				if (isset($_GET["ntcobillingCity"]) && $_GET["ntcobillingCity"]) {
					$order_query_args["billing_city"] = sanitize_text_field($_GET["ntcobillingCity"]);
				}
			}
			return $order_query_args;
		}
		public function filterquery($admin_query) {
			global $pagenow;
			global $typenow;
			if (is_admin() && $admin_query->is_main_query() && in_array($pagenow, ["edit.php", "upload.php"]) && in_array($typenow, $this->post_type_allow) && (!empty($_GET["ntcoDateFrom"]) || !empty($_GET["ntcoDateTo"]))) {
				$admin_query->set("date_query", ["after" => isset($_GET["ntcoDateFrom"]) ? $_GET["ntcoDateFrom"] : "", "before" => isset($_GET["ntcoDateTo"]) ? $_GET["ntcoDateTo"] : "", "inclusive" => true, "column" => "post_date"]);
			}
			if (is_admin() && $admin_query->is_main_query() && in_array($pagenow, ["edit.php", "upload.php"]) && in_array($typenow, $this->post_type_allow) && !empty($_GET["ntcobillingState"])) {
				$meta_query = $admin_query->get("meta_query");
				if ($meta_query && is_array($meta_query)) {
					$meta_query["relation"] = "AND";
					$meta_query[] = ["key" => "_billing_state", "value" => sanitize_text_field($_GET["ntcobillingState"])];
				} else {
					$meta_query = [["key" => "_billing_state", "value" => sanitize_text_field($_GET["ntcobillingState"])]];
				}
				if (!empty($_GET["ntcobillingCity"])) {
					$meta_query[] = ["key" => "_billing_city", "value" => sanitize_text_field($_GET["ntcobillingCity"])];
				}
				$admin_query->set("meta_query", $meta_query);
			}
			return $admin_query;
		}
	}
	new ntcoDateRange();
}
