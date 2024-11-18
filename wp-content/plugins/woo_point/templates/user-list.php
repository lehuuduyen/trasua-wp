<?php


$status = [
  'PURCHASE' => 1,
  'USE_POINT' => 2,
  'USE_POINT_IN_PROCESS' => 4,
];

$currentPage = (! empty($_GET['paged'])) && ($_GET['tab'] == 'dstv') ? (int) $_GET['paged'] : 1;
$total = count($users);
$perPage = 10;
$totalPages = ceil($total / $perPage);
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
          'award' => $history['award'],
          'total_order' => $history['total_order']
        )
      );
    }
  }
}

$result = array();
foreach ($histories as $history) {
  $result[$history['id']][] = $history;
}

$finalResult = [];
$diem=[];
foreach ($result as $key => $value) {
  $finalResult[$key]['id'] = $key;
  $spendingPoint = 0;
  $chiTieu = 0;
  $pointRank = 0;
  $diem[$key]['list_doi_qua']= [];

  foreach ($value as $key2 => $val2) {
    if ($val2['status'] == $status['PURCHASE']) {
      $spendingPoint += $val2['point'];
      $chiTieu += $val2['total_order'];
      $pointRank += $val2['total_order'];
    }
    if ($val2['status'] == $status['USE_POINT'] || $val2['status'] == $status['USE_POINT_IN_PROCESS']) {
      $list['diem'] = $val2['point'];
      $list['award'] = $val2['award'];
      $diem[$key]['list_doi_qua'][]= $list;
      
      $spendingPoint -= $val2['point'];
    }
  }
  $diem[$key]['chi_tieu']= $chiTieu;
  $diem[$key]['doi_qua']= $spendingPoint;
  $diem[$key]['point_rank']= $pointRank;
  $finalResult[$key]['point_rank'] = $pointRank;
}
?>

<table class="wp-list-table widefat fixed striped table-view-list users">
  <thead>
    <tr>
      <th>Tên</th>
      <th>Xếp hạng</th>
      <th>Tiền chi tiêu</th>
      <th>Điểm đổi quà</th>
      <th>Hàng động</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $key => $user) { ?>
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
          <?=(isset($diem[$user['ID']]['chi_tieu']))?$diem[$user['ID']]['chi_tieu']:0?>
        </td>
        <td>
        <?=(isset($diem[$user['ID']]['doi_qua']))?$diem[$user['ID']]['doi_qua']:0?>

        </td>
        <td class="table-actions">
          <span onclick="openEditPointModal(<?php echo $user['ID']; ?>)" class="button dashicons dashicons-edit-page"></span>
        </td>

      </tr>

    <?php } ?>
  </tbody>
</table>
<?php foreach ($users as $key => $user) { ?>

  <div id="modal-edit-point-<?php echo $user['ID']; ?>" class="modal modal-edit-point d-none ">
    <div class="modal-wrapper">
      <p onclick="hideModal('modal-edit-point-<?php echo $user['ID']; ?>')" class="close">✕</p>
      <div class="modal-header">
        <p>Cập nhật điểm cho <?= $user['display_name'] . "-" . $user['ID'] ?></p>
      </div>
      <form action="" name="" method="POST">

        <div class="modal-content">
        <hr />
          
          <div class="form-group">
            <label for="exampleInputEmail1">Số tiền chi tiêu</label>
            <input type="text" class="form-control" style="    margin-left: 23px;" name="point_total" id="point_total" value="<?=(isset($diem[$user['ID']]['chi_tieu']))?$diem[$user['ID']]['chi_tieu']:0?>" disabled>
          </div>
          <br>
          <div class="form-group">
            <label for="exampleInputEmail1">Số điểm đổi quà</label>
            <input type="text" style="    margin-left: 23px;" class="form-control" name="point_award" id="point_award" value="<?=(isset($diem[$user['ID']]['doi_qua']))?$diem[$user['ID']]['doi_qua']:0?>" disabled>
          </div>
          <br>

          <div class="form-group">
            <label for="exampleInputEmail1">Đổi quà mấy điểm</label>
            <input type="number" style="margin-left: 12px;" name="point" id="point" min="0" max="<?=(isset($diem[$user['ID']]['doi_qua']))?$diem[$user['ID']]['doi_qua']:0?>" class="form-control" value="" >
          </div>
          <br>
          <input type="hidden" name="userId" value="<?=$user['ID']?>">
          <div class="form-group">
            <label for="exampleInputEmail1">Quà muốn đổi</label>
            <input style="margin-left: 33px;" name="award" id="award" type="text" class="form-control" value="" >
          </div>
          
          <button  type="submit" name="submit_doiqua" id="modal-next-edit-step-1-all" class="button button-primary">Tiếp theo</button>

          <hr />
         
          <div class="step-content-content" style="overflow-x:auto;">
            <table class="wp-list-table widefat striped table-view-list">
              <thead>
                <tr>
                  <th>Số điểm chi tiêu</th>
                  <th>Chi tiết đổi quà</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if(isset($diem[$user['ID']])){
                  foreach($diem[$user['ID']]['list_doi_qua'] as $qua){
                    ?>
  
                      <tr>
                        <td><?=$qua['diem']?></td>
                        <td><?=$qua['award']?></td>
                      </tr>
                    <?php
                  }
                  
                }
                
                ?>
                
              </tbody>
            </table>
          </div>

        </div>


      </form>
    </div>
  </div>
<?php } ?>

<ul class="pagination">
  <?php
  if ((! empty($_GET['paged'])) && ($_GET['tab'] == 'dstv')) $pg = $_GET['paged'];
  else $pg = 1;

  if (isset($pg) && $pg > 1) {
    echo '<li><a class="button" href="' . site_url() . '/wp-admin/admin.php?page=tich-diem&paged=' . ($pg - 1) . '&tab=dstv">«</a></li>';
  }

  for ($i = 1; $i <= $totalPages; $i++) {
    if (isset($pg) && $pg == $i)  $active = 'active';
    else $active = '';
    echo '<li><a href="' . site_url() . '/wp-admin/admin.php?page=tich-diem&paged=' . $i . '&tab=dstv" class="button ' . $active . '">' . $i . '</a></li>';
  }

  if (isset($pg) && $pg < $totalPages) {
    echo '<li><a class="button" href="' . site_url() . '/wp-admin/admin.php?page=tich-diem&paged=' . ($pg + 1) . '&tab=dstv">»</a></li>';
  }
  ?>
</ul>