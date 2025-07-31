<?php
  $userCommissions = $wpdb->get_results( 'SELECT * FROM ' . $tableUserCommission . ' ORDER BY id ASC', ARRAY_A );
  $userCommissionsStatus4 = [];

  $conditionSetting3 = '';

  if (isset($_GET['choDoiSoat'])) {
    $conditionSetting3 = $status['USE_POINT_IN_PROCESS'];
  }

  if (isset($_GET['daThanhToan'])) {
    $conditionSetting3 = $status['USE_POINT'];
  }

  if (isset($_GET['huyThanhToan'])) {
    $conditionSetting3 = $status['CANCEL'];
  }
  
  foreach ( $userCommissions as $userCommission ) {
    $userInfo = get_userdata($userCommission['user_id']);
    if ($conditionSetting3) {
      if ($userCommission['status'] == $conditionSetting3) {
        $temp = $userCommission;
        $paymentMethod = json_decode($userCommission['payment_method'], true);
        $temp['display_name'] = $userInfo->user_nicename . ' - ' . $userInfo->user_login;
        if (is_array($paymentMethod) && count($paymentMethod) > 0) {
          $temp['woo_aff_name'] = $paymentMethod['name'];
          $temp['woo_aff_stk'] = $paymentMethod['stk'];
          $temp['woo_aff_bankname'] = $paymentMethod['bankname'];
        } else {
          $temp['woo_aff_name'] = '';
          $temp['woo_aff_stk'] = '';
          $temp['woo_aff_bankname'] = '';
        }
        
        array_push($userCommissionsStatus4, $temp);
      }
    } else {
      if ($userCommission['status'] == $status['USE_POINT_IN_PROCESS'] || $userCommission['status'] == $status['USE_POINT'] || $userCommission['status'] == $status['CANCEL']) {
        $temp = $userCommission;
        $paymentMethod = json_decode($userCommission['payment_method'], true);
        $temp['display_name'] = $userInfo->user_nicename . ' - ' . $userInfo->user_login;
        if (is_array($paymentMethod) && count($paymentMethod) > 0) {
          $temp['woo_aff_name'] = $paymentMethod['name'];
          $temp['woo_aff_stk'] = $paymentMethod['stk'];
          $temp['woo_aff_bankname'] = $paymentMethod['bankname'];
        } else {
          $temp['woo_aff_name'] = '';
          $temp['woo_aff_stk'] = '';
          $temp['woo_aff_bankname'] = '';
        }
        
        array_push($userCommissionsStatus4, $temp);
      }
    }
  }

  $currentPage = (! empty( $_GET['paged'] )) && ($_GET['tab'] == 'setting3') ? (int) $_GET['paged'] : 1;
  $total = count( $userCommissionsStatus4 );
  $perPage = 10;
  $totalPages = ceil($total/ $perPage);
  $currentPage = max($currentPage, 1);
  $currentPage = min($currentPage, $totalPages);
  $offset = ($currentPage - 1) * $perPage;
  if ($offset < 0) $offset = 0;
  $userCommissionsStatus4 = array_slice($userCommissionsStatus4, $offset, $perPage);
?>

<div class="flex-center">
  <form action="" method="GET">
    <input type="hidden" name="page" value="hoa-hong" />
    <input type="hidden" name="paged" value="1" />
    <input type="hidden" name="tab" value="setting3" />
    <button type="submit" name="choDoiSoat" class="button button-primary">Chờ đối soát</button>
  </form>
  <form action="" method="GET">
    <input type="hidden" name="page" value="hoa-hong" />
    <input type="hidden" name="paged" value="1" />
    <input type="hidden" name="tab" value="setting3" />
    <button type="submit" name="daThanhToan" class="button button-primary">Đã thanh toán</button>
  </form>
  <form action="" method="GET">
    <input type="hidden" name="page" value="hoa-hong" />
    <input type="hidden" name="paged" value="1" />
    <input type="hidden" name="tab" value="setting3" />
    <button type="submit" name="huyThanhToan" class="button button-primary">Huỷ</button>
  </form>
  <form action="" method="GET">
    <input type="hidden" name="page" value="hoa-hong"/>
    <input type="hidden" name="paged" value="1" />
    <input type="hidden" name="tab" value="setting3" />
    <button type="submit" class="button button-primary">Bỏ lọc</button>
  </form>
</div>

<br />

<table class="wp-list-table widefat fixed striped table-view-list users">
  <thead>
    <tr>
      <th>Tên người dùng</th>
      <th>Tên người dùng (tài khoản)</th>
      <th>Số tài khoản</th>
      <th>Ngân hàng</th>
      <th>Số tiền</th>
      <th>Trạng thái</th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach ($userCommissionsStatus4 as $userCommissionStatus4) {
    ?>
      <tr>
        <td><?php echo $userCommissionStatus4['display_name'] ?></td>
        <td><?php echo $userCommissionStatus4['woo_aff_name'] ?></td>
        <td><?php echo $userCommissionStatus4['woo_aff_stk'] ?></td>
        <td><?php echo $userCommissionStatus4['woo_aff_bankname'] ?></td>
        <td><?php echo $userCommissionStatus4['commission'] ?></td>
        <td>
          <?php
            if ($userCommissionStatus4['status'] == $status['USE_POINT_IN_PROCESS']) {
          ?>
            <form action="?page=hoa-hong&paged=1&tab=setting3" method="POST">
              <select name="status">
                <option value="<?php echo $status['USE_POINT_IN_PROCESS'] ; ?>">Chờ đối soát</option>
                <option value="<?php echo $status['USE_POINT'] ; ?>">Xác nhận</option>
                <option value="<?php echo $status['CANCEL'] ; ?>">Huỷ</option>
              </select>
              <input hidden name="userCommissionId" value="<?php echo $userCommissionStatus4['id'] ?>" />
              <button type="submit" name="updateStatus" class="button">Cập nhật</button>
            </form>
            <?php } else if ($userCommissionStatus4['status'] == $status['USE_POINT']) { ?>
              <p>Đã thanh toán</p>
            <?php } else { ?>
              <p>Đã huỷ</p>
            <?php } ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
<ul class="pagination">
  <?php
    if ( (! empty( $_GET['paged'] )) && ($_GET['tab'] == 'setting3') ) $pg = $_GET['paged'];
    else $pg = 1;
    $paramFilterSetting3 = '';
    if (isset($_GET['choDoiSoat'])) {
      $paramFilterSetting3 = '&choDoiSoat';
    }
    if (isset($_GET['daThanhToan'])) {
      $paramFilterSetting3 = '&daThanhToan';
    }

    if ( isset( $pg ) && $pg > 1 ) {
      echo '<li><a class="button" href="'.site_url().'/wp-admin/admin.php?page=hoa-hong&paged=' . ( $pg - 1 ) . '&tab=setting3' . $paramFilterSetting3 . '">«</a></li>';
    }

    for ( $i = 1; $i <= $totalPages; $i++ ) {
      if ( isset( $pg ) && $pg == $i )  $active = 'active';
      else $active = '';
      echo '<li><a href="'.site_url().'/wp-admin/admin.php?page=hoa-hong&paged=' . $i . '&tab=setting3' . $paramFilterSetting3 . '" class="button ' . $active . '">' . $i . '</a></li>';
    }

    if ( isset( $pg ) && $pg < $totalPages ) {
      echo '<li><a class="button" href="'.site_url().'/wp-admin/admin.php?page=hoa-hong&paged=' . ( $pg + 1 ). '&tab=setting3' . $paramFilterSetting3 . '">»</a></li>';
    }
  ?>
</ul>
