<?php
//Thêm mục vào Dashboard
function builder_dashboard_widgets()
{
	wp_add_dashboard_widget('builder_dashboard_widgets', 'Thông tin về website', 'builder_dashboard_widgets_body');

	global $wp_meta_boxes;
	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
	$example_widget_backup = array('builder_dashboard_widgets' => $normal_dashboard['builder_dashboard_widgets']);
	unset($normal_dashboard['builder_dashboard_widgets']);
	$sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);
	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}

function builder_dashboard_widgets_body()
{
	if (BUILDER_EXPIRED > 0) {
		$expiredDays = floor((BUILDER_EXPIRED - time()) / 3600 / 24);
		echo '
		<div style="color: ' . ($expiredDays > 7 ? '#218bd1' : 'red') . '">
			<div style="font-size: 17px;padding: 10px 0">
				<i class="fa fa-calendar-times-o"></i> Hạn thanh toán: 
				<b>
					' . date("d/m/Y", (BUILDER_EXPIRED - (3600 * 24))) . '
					' . ($expiredDays < 60 ? '(còn ' . ($expiredDays - 1) . ' ngày)' : '') . '
				</b>
			</div>
		</div>
		';
		if (BUILDER_IS_OF_MEMBER) {
			echo '
				<div style="padding: 10px 0">
					<a class="button button-primary" target="_blank" href="' . BUILDER_HOME . '/admin/WebsiteList"><i class="fa fa-wrench"></i> Quản lý WEB & gia hạn tại đây</a>
				</div>
			';
		}

		if ($expiredDays < 60) {
			echo '
			<div style="color: red;padding: 10px 0">
				<i class="fa fa-warning"></i> CẢNH BÁO: Website sẽ bị xóa vĩnh viễn nếu quý khách không gia hạn trước ngày: <b>' . date("d/m/Y", (BUILDER_EXPIRED - (3600 * 24))) . '</b>
			</div>
			';
		}
	}

	//Thống kê dung lượng
	$diskUsed = folderSize(PUBLIC_ROOT);
	$percent = $diskUsed / mb2Bytes(BUILDER_MAXDISK) * 100;
	$percent = round($percent);
	echo '
	<script src="' . BUILDER_HOME . '/web-builder/assets/chart/progress.js"></script>
	<link href="' . BUILDER_HOME . '/web-builder/assets/chart/progress.css" rel="stylesheet" />
	<div class="progress-pie" data-color="" data-value="' . $percent . '" data-size="250">
		<p>
			<b style="font-size: 30px">Lưu trữ</b><br/>
			' . bytesConvert($diskUsed) . ' / ' . bytesConvert(mb2Bytes(BUILDER_MAXDISK)) . '<br/>
			Còn trống ' . (100 - $percent) . '%
		</p>
	</div>
	';
	if (BUILDER_IS_OF_MEMBER) {
		echo '
			<div style="padding-bottom: 10px; text-align: center">
				<a class="btn-info" target="_blank" href="' . BUILDER_HOME . '/admin/WebsiteList"><i class="fa fa-plus"></i> Mua thêm dung lượng</a>
			</div>
		';
	}
	if ((100 - $percent) <= 10) {
		echo '<div class="alert-danger">Vui lòng xóa bớt tệp tin hoặc nâng cấp thêm dung lượng!</div>';
	}
}

add_action("wp_dashboard_setup", "builder_dashboard_widgets");