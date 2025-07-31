<?php
$arrayCommission = [];
$totalCommission = 0;
foreach ($users as $keyUser => $user) {
  $waitingReview = $wpdb->get_results('SELECT SUM(commission) as waitingReview FROM ' . $tableUserCommission . ' WHERE user_id = ' . $user['ID'] . ' AND status = ' . $status['USE_POINT_IN_PROCESS']);
  $actuallyReceive = $wpdb->get_results('SELECT SUM(commission) as actuallyReceive FROM ' . $tableUserCommission . ' WHERE user_id = ' . $user['ID'] . ' AND status = ' . $status['USE_POINT']);
  $commissions = [];
  $countChild = 0;
  foreach ($userCommissions as $commission1) {
    if ($user['ID'] === $commission1['user_id']) {
      array_push(
        $commissions,
        array(
          'id' => $commission1['user_id'],
          'status' => $commission1['status'],
          'commission' => $commission1['commission'],
          'total_order' => $commission1['total_order']
        )
      );
    }
  }

  $result = array();
  foreach ($commissions as $commission2) {
    $result[$commission2['id']][] = $commission2;
  }

  $commissionParent = 0;
  foreach ($result as $key => $value) {
    foreach ($value as $val2) {
      if ($val2['status'] == $status['USE_POINT'] || $val2['status'] == $status['USE_POINT_IN_PROCESS']) {
        $commissionParent += $val2['commission'];
      }
    }
  }

  $childCommissions = 0;
  $totalRevenue = 0;
  $totalOrder = 0;

  foreach ($userCommissions as $commission4) {
    if ($commission4['user_parent'] === $user['ID'] && $commission4['status'] == $status['PURCHASE']) {
      $childCommissions += $commission4['commission'];
      $totalRevenue += $commission4['total_order'];
      $totalOrder++;
    }
  }

  array_push($arrayCommission, [
    'id' => $user['ID'],
    'commission' => $childCommissions - $commissionParent,
    'user_nicename' => $user['user_nicename'],
    'user_login' => $user['user_login'],
    'countChild' => $countChild
  ]);
  $totalCommission += $childCommissions - $commissionParent;
}
?>

<br />
<div class="flex-center space-between">
  <div class="flex-center">
    <form action="" method="GET" class="flex-center">
      <input type="hidden" name="page" value="hoa-hong" />
      <input type="hidden" name="paged" value="1" />
      <input type="hidden" name="tab" value="setting1" />
      <input type="text" maxlength="48" placeholder="Điền tên user muốn tìm" name="username" value="<?php echo isset($_GET['username']) ? $_GET['username'] : ''; ?>" />
      <button type="submit" name="searchUser" class="button button-primary">Tìm kiếm</button>
    </form>
    <!-- <form action="" method="GET">
      <input type="hidden" name="page" value="hoa-hong"/>
      <input type="hidden" name="paged" value="1"/>
      <input type="hidden" name="tab" value="setting1"/>
      <button type="submit" name="topCommmission" class="button button-primary">Top Hoa Hồng</button>
    </form>
    <form action="" method="GET">
      <input type="hidden" name="page" value="hoa-hong"/>
      <input type="hidden" name="paged" value="1"/>
      <input type="hidden" name="tab" value="setting1"/>
      <button type="submit" name="topIntroduce" class="button button-primary">Top Giới thiệu</button>
    </form>
    <form action="" method="GET">
      <input type="hidden" name="page" value="hoa-hong"/>
      <input type="hidden" name="paged" value="1"/>
      <input type="hidden" name="tab" value="setting1"/>
      <button type="submit" class="button button-primary">Bỏ tìm kiếm</button>
    </form> -->
  </div>
  <div class="flex-center">
    <div>
      <p style="margin-top: 0; margin-bottom: 2px;"><b>Thành viên</b></p>
      <input type="text" readonly class="button" style="cursor: default;" value="<?php echo count($users); ?>" />
    </div>
    <div>
      <p style="margin-top: 0; margin-bottom: 2px;"><b>Tổng hoa hồng</b></p>
      <input type="text" readonly class="button" style="cursor: default;" value="<?php echo $totalCommission; ?>" />
    </div>
  </div>
</div>
<br />
<table class="wp-list-table widefat fixed striped table-view-list users">
  <thead>
    <tr>
      <th>Số thứ tự</th>
      <th>Tên</th>
      <th>Chờ đối soát<span class="d-none"><br /> (sum commission status 4 (CURRENT USER))</span></th>
      <th>Thực nhận <span class="d-none"><br /> (sum commission status 2 (CURRENT USER))</span></th>
      <th>Hoa hồng <span class="d-none"><br /> tổng commission status 1 <b style="color: red;">(USER CON)</b> <br /> - tổng (commission status 2 <b style="color: blue">CURRENT USER</b>) <br /> - tổng (commission status 4 <b style="color: blue">CURRENT USER</b>))</span></th>
      <th>Tổng hoa hồng <span class="d-none"><br /> sum (commision status 1 (<b style="color: red;">USER CON</b>))</span></th>
      <th>Tổng hoa hồng đã rút <span class="d-none"><br /> (sum commission status 2 (CURRENT USER))</span></th>
      <th>Tổng doanh thu <span class="d-none"><br /> sum (total_order status 1 (<b style="color: red;">USER CON</b>))</span></th>
      <th>Tổng đơn hàng <span class="d-none"><br /> <b style="color: green">tổng số dòng (row) </b>status 1 (<b style="color: red;">USER CON</b>)</span></th>
      <th>User con</th>
    </tr>
  </thead>
  <tbody>
    <?php
    foreach ($usersDisplay as $keyUser => $user) {
      $waitingReview = $wpdb->get_results('SELECT SUM(commission) as waitingReview FROM ' . $tableUserCommission . ' WHERE user_id = ' . $user['ID'] . ' AND status = ' . $status['USE_POINT_IN_PROCESS']);
      $actuallyReceive = $wpdb->get_results('SELECT SUM(commission) as actuallyReceive FROM ' . $tableUserCommission . ' WHERE user_id = ' . $user['ID'] . ' AND status = ' . $status['USE_POINT']);
      $commissions = [];
      $countChild = 0;
      foreach ($userCommissions as $commission1) {
        if ($user['ID'] === $commission1['user_id']) {
          array_push(
            $commissions,
            array(
              'id' => $commission1['user_id'],
              'status' => $commission1['status'],
              'commission' => $commission1['commission'],
              'total_order' => $commission1['total_order']
            )
          );
        }
      }

      $result = array();
      foreach ($commissions as $commission2) {
        $result[$commission2['id']][] = $commission2;
      }

      $commissionParent = 0;
      foreach ($result as $key => $value) {
        foreach ($value as $val2) {
          if ($val2['status'] == $status['USE_POINT'] || $val2['status'] == $status['USE_POINT_IN_PROCESS']) {
            $commissionParent += $val2['commission'];
          }
        }
      }

      $childCommissions = 0;
      $totalRevenue = 0;
      $totalOrder = 0;
      
      foreach ($userCommissions as $commission4) {
        if ($commission4['user_parent'] === $user['ID'] && $commission4['status'] == $status['PURCHASE']) {
          $childCommissions += $commission4['commission'];
          $totalRevenue += $commission4['total_order'];
          $totalOrder++;
        }
      }

      $stt++;
      if (!isset($_GET['topCommmission']) && !isset($_GET['topIntroduce'])) {
    ?>
        <tr>
          <td><?php echo $stt + $paged - 1; ?></td>
          <td><?php echo $user['user_nicename'] . ' - ' . $user['user_login'] ?></td>
          <td><?php echo $waitingReview[0]->waitingReview; ?></td>
          <td><?php echo $actuallyReceive[0]->actuallyReceive; ?></td>
          <td><?php echo $childCommissions - $commissionParent; ?></td>
          <td><?php echo $childCommissions; ?></td>
          <td><?php echo $actuallyReceive[0]->actuallyReceive; ?></td>
          <td><?php echo $totalRevenue; ?></td>
          <td><?php echo $totalOrder; ?></td>
          <td>
            <button type="button" class="button" onclick="openLowerModal('<?php echo $user['ID']; ?>')">Hiển thị cấp dưới</button>
          </td>
        </tr>
      <?php }
    }
    if (isset($_GET['topCommmission']) || isset($_GET['topIntroduce'])) {
      $max = $arrayCommission[0];
      $tempMaxCommission = $arrayCommission[0]['commission'];
      $tempMaxIntroduce = $arrayCommission[0]['countChild'];

      if (isset($_GET['topCommmission'])) {
        foreach ($arrayCommission as $arrCom) {
          if ($arrCom['commission'] >= $tempMaxCommission) {
            $max = $arrCom;
            $tempMaxCommission = $arrCom['commission'];
          }
        }
      }

      if (isset($_GET['topIntroduce'])) {
        foreach ($arrayCommission as $arrCom) {
          if ($arrCom['countChild'] >= $tempMaxIntroduce) {
            $max = $arrCom;
            $tempMaxIntroduce = $arrCom['countChild'];
          }
        }
      }

      $waitingReview = $wpdb->get_results('SELECT SUM(commission) as waitingReview FROM ' . $tableUserCommission . ' WHERE user_id = ' . $max['id'] . ' AND status = ' . $status['USE_POINT_IN_PROCESS']);
      $actuallyReceive = $wpdb->get_results('SELECT SUM(commission) as actuallyReceive FROM ' . $tableUserCommission . ' WHERE user_id = ' . $max['id'] . ' AND status = ' . $status['USE_POINT']);
      $commissions = [];
      foreach ($userCommissions as $commission1) {
        if ($max['id'] === $commission1['user_id']) {
          array_push(
            $commissions,
            array(
              'id' => $commission1['user_id'],
              'status' => $commission1['status'],
              'commission' => $commission1['commission'],
              'total_order' => $commission1['total_order']
            )
          );
        }
      }

      $result = array();
      foreach ($commissions as $commission2) {
        $result[$commission2['id']][] = $commission2;
      }

      $commissionParent = 0;
      foreach ($result as $key => $value) {
        foreach ($value as $val2) {
          if ($val2['status'] == $status['USE_POINT'] || $val2['status'] == $status['USE_POINT_IN_PROCESS']) {
            $commissionParent += $val2['commission'];
          }
        }
      }

      $childCommissions = 0;
      $totalRevenue = 0;
      $totalOrder = 0;
      foreach ($userCommissions as $commission4) {
        if ($commission4['user_parent'] === $max['ID'] && $commission4['status'] == $status['PURCHASE']) {
          $childCommissions += $commission4['commission'];
          $totalRevenue += $commission4['total_order'];
          $totalOrder++;
        }
      }


      ?>
      <tr>
        <td>1</td>
        <td><?php echo $max['user_nicename'] . ' - ' . $max['user_login'] ?></td>
        <td><?php echo $waitingReview[0]->waitingReview; ?></td>
        <td><?php echo $actuallyReceive[0]->actuallyReceive; ?></td>
        <td><?php echo $childCommissions - $commissionParent; ?></td>
        <td><?php echo $childCommissions; ?></td>
        <td><?php echo $actuallyReceive[0]->actuallyReceive; ?></td>
        <td><?php echo $totalRevenue; ?></td>
        <td><?php echo $totalOrder; ?></td>
        <td>
          <button type="button" class="button" onclick="openLowerModal('<?php echo $max['id']; ?>')">Hiển thị cấp dưới</button>
        </td>
      </tr>
    <?php
    }
    ?>
  </tbody>
</table>
<?php if (!isset($_GET['topCommmission']) && !isset($_GET['topIntroduce'])) { ?>
  <ul class="pagination">
    <?php
    if ((!empty($_GET['paged'])) && ($_GET['tab'] == 'setting1')) $pg = $_GET['paged'];
    else $pg = 1;
    $paramFilter = isset($_GET['searchUser']) && isset($_GET['username']) ? '&username=' . $_GET['username'] . '&searchUser' : '';

    if (isset($pg) && $pg > 1) {
      echo '<li><a class="button" href="' . site_url() . '/wp-admin/admin.php?page=hoa-hong&paged=' . ($pg - 1) . '&tab=setting1' . $paramFilter . '">«</a></li>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
      if (isset($pg) && $pg == $i)  $active = 'active';
      else $active = '';
      echo '<li><a href="' . site_url() . '/wp-admin/admin.php?page=hoa-hong&paged=' . $i . '&tab=setting1' . $paramFilter . '" class="button ' . $active . '">' . $i . '</a></li>';
    }

    if (isset($pg) && $pg < $totalPages) {
      echo '<li><a class="button" href="' . site_url() . '/wp-admin/admin.php?page=hoa-hong&paged=' . ($pg + 1) . '&tab=setting1' . $paramFilter . '">»</a></li>';
    }
    ?>
  </ul>
<?php } ?>