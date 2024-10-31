<br />
<form action="?page=hoa-hong&paged=1&tab=setting4" method="GET">
  <input type="hidden" name="page" value="hoa-hong"/>
  <input type="hidden" name="paged" value="1"/>
  <input type="hidden" name="tab" value="setting4"/>
  <b>Từ ngày</b>
  <select name="dateFrom" onchange="handleSubmit()">
    <option value="" disabled selected>Chọn ngày</option>
    <?php
      for ($i = 1; $i <= 31; $i++) { ?>
        <option <?php echo isset($_GET['dateFrom']) && $_GET['dateFrom'] == $i ? 'selected' : '' ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
    <?php }?>
  </select>
  <select name="monthFrom" onchange="handleSubmit()">
    <option value="" disabled selected>Chọn tháng</option>
    <?php
      for ($i = 1; $i <= 12; $i++) { ?>
        <option <?php echo isset($_GET['monthFrom']) && $_GET['monthFrom'] == $i ? 'selected' : '' ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
    <?php }?>
  </select>
  <select name="yearFrom" onchange="handleSubmit()">
    <option value="" disabled selected>Chọn năm</option>
    <?php
      $currentYear = date("Y");
      for ($i = $currentYear - 10; $i <= $currentYear; $i++) { ?>
        <option <?php echo isset($_GET['yearFrom']) && $_GET['yearFrom'] == $i ? 'selected' : '' ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
    <?php }?>
  </select>
  &nbsp;&nbsp;&nbsp;<b>Đến ngày</b>
  <select name="dateTo" onchange="handleSubmit()">
    <option value="" disabled selected>Chọn ngày</option>
    <?php
      for ($i = 1; $i <= 31; $i++) { ?>
        <option <?php echo isset($_GET['dateTo']) && $_GET['dateTo'] == $i ? 'selected' : '' ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
    <?php }?>
  </select>
  <select name="monthTo" onchange="handleSubmit()">
    <option value="" disabled selected>Chọn tháng</option>
    <?php
      for ($i = 1; $i <= 12; $i++) { ?>
        <option <?php echo isset($_GET['monthTo']) && $_GET['monthTo'] == $i ? 'selected' : '' ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
    <?php }?>
  </select>
  <select name="yearTo" onchange="handleSubmit()">
    <option value="" disabled selected>Chọn năm</option>
    <?php
      $currentYear = date("Y");
      for ($i = $currentYear - 10; $i <= $currentYear; $i++) { ?>
        <option <?php echo isset($_GET['yearTo']) && $_GET['yearTo'] == $i ? 'selected' : '' ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
    <?php }?>
  </select>
  <button type="submit" name="userFilter" class="button button-primary submitFilter" disabled>Lọc</button>
</form>
<br />
<table class="wp-list-table widefat fixed striped table-view-list users">
  <thead>
    <tr>
      <th>Tên</th>
      <th>Tổng hoa hồng đã rút <span class="d-none"><br /> (sum commission status 2 (CURRENT USER))</span></th>
      <th>Tổng doanh thu <span class="d-none"><br /> sum (total_order status 1 (<b style="color: red;">USER CON</b>))</span></th>
      <th>Tổng đơn hàng <span class="d-none"><br /> sum (row status 1 (<b style="color: red;">USER CON:</b>))</span></th>
    </tr>
  </thead>
  <tbody>
    <?php
      $users = $wpdb->get_results( 'SELECT * FROM ' . $tableUser . ' WHERE user_login != "77777777" ORDER BY id ASC', ARRAY_A );
      $currentPage = (! empty( $_GET['paged'] )) && ($_GET['tab'] == 'setting4') ? (int) $_GET['paged'] : 1;
      $total = count( $users );
      $perPage = 10;
      $totalPages = ceil($total/ $perPage);
      $currentPage = max($currentPage, 1);
      $currentPage = min($currentPage, $totalPages);
      $offset = ($currentPage - 1) * $perPage;
      $users = array_slice($users, $offset, $perPage);

      foreach ( $users as $userFilter ) {
        $queryActuallyRecieve = 'SELECT SUM(commission) as actuallyReceive FROM ' . $tableUserCommission . ' WHERE user_id = ' . $userFilter['ID'] . ' AND status = ' . $status['USE_POINT'];
        if (isset($_GET['userFilter'])) {
          $queryActuallyRecieve .= " AND STR_TO_DATE(CONCAT_WS('/', `date`, `month`, `year`), '%d/%m/%Y') BETWEEN '" . $_GET['yearFrom'] . '-' . $_GET['monthFrom'] . '-' . $_GET['dateFrom'] . "'" . " AND '" . $_GET['yearTo'] . '-' . $_GET['monthTo'] . '-' . $_GET['dateTo'] . "'";
        }
        $actuallyReceive = $wpdb->get_results($queryActuallyRecieve);

        $queryUserCommission = 'SELECT * FROM ' . $tableUserCommission;
        if (isset($_GET['userFilter'])) {
          $queryUserCommission .= " WHERE STR_TO_DATE(CONCAT_WS('/', `date`, `month`, `year`), '%d/%m/%Y') BETWEEN '" . $_GET['yearFrom'] . '-' . $_GET['monthFrom'] . '-' . $_GET['dateFrom'] . "'" . " AND '" . $_GET['yearTo'] . '-' . $_GET['monthTo'] . '-' . $_GET['dateTo'] . "'";
        }
    
        $totalRevenue = 0;
        $totalOrder = 0;
        foreach ($users as $userChild) {
          $checkUserParent = get_user_meta($userChild['ID'], 'user_parent', true);
          if ($userFilter['ID'] === $checkUserParent) {
            foreach ($userCommissions as $commission4) {
              if ($commission4['user_id'] === $userChild['ID'] && $commission4['status'] == $status['PURCHASE']) {
                $totalRevenue += $commission4['total_order'];
                $totalOrder++;
              }
            }
            foreach ($users as $userChildLevel2) {
              $checkUserParentLevel2 = get_user_meta($userChildLevel2['ID'], 'user_parent', true);
              if ($userChild['ID'] === $checkUserParentLevel2) {
                foreach ($userCommissions as $commission5) {
                  if ($commission5['user_id'] === $userChildLevel2['ID'] && $commission5['status'] == $status['PURCHASE']) {
                    $totalRevenue += $commission5['total_order'];
                    $totalOrder++;
                  }
                }
              }
            }
          }
        }
    ?>
      <tr>
        <td><?php echo $userFilter['user_nicename'] . ' - ' . $userFilter['user_login'] ?></td>
        <td><?php echo $actuallyReceive[0]->actuallyReceive; ?></td>
        <td><?php echo $totalRevenue; ?></td>
        <td><?php echo $totalOrder; ?></td>
      </tr>
    <?php } ?>
  </tbody>
</table>

<ul class="pagination">
  <?php
    if ($offset < 0) $offset = 0;
    if ( (! empty( $_GET['paged'] )) && ($_GET['tab'] == 'setting4') ) $pg = $_GET['paged'];
    else $pg = 1;
    $paramFilter = '';
    if (isset($_GET['userFilter'])) {
      $paramFilter = '&dateFrom=' . $_GET['dateFrom'] . '&monthFrom=' . $_GET['monthFrom'] . '&yearFrom=' . $_GET['yearFrom'] . '&dateTo=' . $_GET['dateTo'] . '&monthTo=' . $_GET['monthTo'] . '&yearTo=' . $_GET['yearTo'] . '&userFilter';
    }

    if ( isset( $pg ) && $pg > 1 ) {
      echo '<li><a class="button" href="'.site_url().'/wp-admin/admin.php?page=hoa-hong&paged=' . ( $pg - 1 ) . '&tab=setting4' . $paramFilter . '">«</a></li>';
    }

    for ( $i = 1; $i <= $totalPages; $i++ ) {
      if ( isset( $pg ) && $pg == $i )  $active = 'active';
      else $active = '';
      echo '<li><a href="'.site_url().'/wp-admin/admin.php?page=hoa-hong&paged=' . $i . '&tab=setting4' . $paramFilter . '" class="button ' . $active . '">' . $i . '</a></li>';
    }

    if ( isset( $pg ) && $pg < $totalPages ) {
      echo '<li><a class="button" href="'.site_url().'/wp-admin/admin.php?page=hoa-hong&paged=' . ( $pg + 1 ). '&tab=setting4' . $paramFilter . '">»</a></li>';
    }
  ?>
</ul>
