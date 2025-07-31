<?php
  $status = [
    'PURCHASE' => 1,
    'USE_POINT' => 2,
    'USE_POINT_IN_PROCESS' => 4,
  ];

  $currentPage = (! empty( $_GET['paged'] )) && ($_GET['tab'] == 'dstv') ? (int) $_GET['paged'] : 1;
  $total = count( $users );
  $perPage = 10;
  $totalPages = ceil($total/ $perPage);
  $currentPage = max($currentPage, 1);
  $currentPage = min($currentPage, $totalPages);
  $offset = ($currentPage - 1) * $perPage;
  if ($offset < 0) $offset = 0;
  $users = array_slice($users, $offset, $perPage);

  $histories = [];
  foreach ($users as $key1 => $user) {
    foreach ($userHistoryPoint as $key2 => $history) {
      if ($user['ID'] === $history['user_id']) {
        array_push(
          $histories,
          array(
            'id' => $history['user_id'],
            'status' => $history['status'],
            'point' => $history['point'],
            'total_order' => $history['total_order']
          ));
      }
    }
  }

  $result = array();
  foreach ($histories as $history) {
    $result[$history['id']][] = $history;
  }

  $finalResult = [];
  
  foreach ($result as $key => $value) {
    $finalResult[$key]['id'] = $key;
    $spendingPoint = 0;
    $pointRank = 0;
    foreach ($value as $key2 => $val2) {
      if ($val2['status'] == $status['PURCHASE']) {
        $spendingPoint += $val2['point'];
        $pointRank += $val2['total_order'];
      }
      if ($val2['status'] == $status['USE_POINT'] || $val2['status'] == $status['USE_POINT_IN_PROCESS']) {
        $spendingPoint -= $val2['point'];
      }
    }
    $finalResult[$key]['spending_point'] = $spendingPoint;
    $finalResult[$key]['point_rank'] = $pointRank;
  }
?>

<table class="wp-list-table widefat fixed striped table-view-list users">
  <thead>
    <tr>
      <th>Tên</th>
      <th>Xếp hạng</th>
      <th>Điểm chi tiêu</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ( $users as $key => $user ) { ?>
      <tr>
        <td><?php echo $user['display_name'] ?></td>
        <td>
          <?php
            $rankDisplay = '';
            foreach ($finalResult as $k => $result) {
              if ($result['id'] == $user['ID']) {
                foreach ($ranks as $keyRank => $rank) {
                  if ($result['point_rank'] >= $rank['minimum_spending']) {
                    $rankDisplay = $rank['name'];
                  }
                }
              }
            }
            echo $rankDisplay ? $rankDisplay : 'Chưa có xếp hạng';
          ?>
        </td>
        <td>
          <?php
            $check = 0;
            foreach ($finalResult as $k => $result) {
              if ($result['id'] == $user['ID']) {
                echo $result['spending_point'] . 'đ';
                $check++;
              }
            }
            if ($check == 0) {
              echo '0đ';
            }
          ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
<ul class="pagination">
  <?php
    if ( (! empty( $_GET['paged'] )) && ($_GET['tab'] == 'dstv') ) $pg = $_GET['paged'];
    else $pg = 1;

    if ( isset( $pg ) && $pg > 1 ) {
      echo '<li><a class="button" href="'.site_url().'/wp-admin/admin.php?page=tich-diem&paged=' . ( $pg - 1 ) . '&tab=dstv">«</a></li>';
    }

    for ( $i = 1; $i <= $totalPages; $i++ ) {
      if ( isset( $pg ) && $pg == $i )  $active = 'active';
      else $active = '';
      echo '<li><a href="'.site_url().'/wp-admin/admin.php?page=tich-diem&paged=' . $i . '&tab=dstv" class="button ' . $active . '">' . $i . '</a></li>';
    }

    if ( isset( $pg ) && $pg < $totalPages ) {
      echo '<li><a class="button" href="'.site_url().'/wp-admin/admin.php?page=tich-diem&paged=' . ( $pg + 1 ). '&tab=dstv">»</a></li>';
    }
  ?>
</ul>