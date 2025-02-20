<?php
foreach ($usersDisplay as $keyUserModal => $user) {
  $childUser = array();
  $tempIds = array();
  
  $userChild = $wpdb->get_results('select '.$tableUser.'.ID,
  '.$tableUser.'.user_login as `mobile`,'.$tableUser.'.user_nicename ,
   SUM('.$tableUserCommission.'.commission) as commission, 
   SUM('.$tableUserCommission.'.total_order) as total_order, 
   '.$tableUserCommission.'.create_at,'.$tableUserCommission.'.product_id  ,'.$tablePost.'.post_title 
   from '.$tableUserCommission.' 
   inner join '.$tableUser.' on '.$tableUser.'.ID = '.$tableUserCommission.'.user_id
   inner join '.$tablePost.' on '.$tablePost.'.ID = '.$tableUserCommission.'.product_id
   where '.$tableUserCommission.'.user_parent = '.$user['ID'].' and 
   '.$tableUserCommission.'.status = 1 
    group by '.$tableUser.'.ID, 
    '.$tableUser.'.user_login, 
    '.$tableUserCommission.'.create_at
    order by '.$tableUserCommission.'.ID desc', ARRAY_A);
    if ($userChild) {
      foreach ($userChild as $child) {
        $tempIds[]=$child['ID'];
        array_push($childUser, $child);
      }
    }
  $userClickShare = $wpdb->get_results('SELECT * FROM ' . $tableShareLink . ' 
  INNER JOIN '.$tableUser.' ON '.$tableUser.'.ID=' . $tableShareLink . '.user_id 
  INNER join '.$tablePost.' ON '.$tablePost.'.ID = '.$tableShareLink.'.product
   where ' . $tableShareLink . '.status != 2 AND user_parent = ' . $user['ID'].' AND ' . $tableShareLink . '.user_id not in ('.implode(",",$tempIds).') 
  group by '.$tableUser.'.ID, 
    '.$tableUser.'.user_login, 
    '.$tableShareLink.'.create_at ', ARRAY_A);
  $tempClick =[];
  foreach($userClickShare as $val){
      if(!in_array($val['ID'],$tempClick)){
          $tempClick[]=$val['ID'];
          $val['commission'] = 0;
          $val['total_order'] = 0;
          $val['product_id'] = $val['product'];
          array_push($childUser, $val);

      }
  }
 


?>
  <div class="modal d-none modal-lower-level-<?php echo $user['ID']; ?>">
    <div class="modal-wrapper">
      <p onclick="closeLowerModal('<?php echo $user['ID']; ?>')" class="close">✕</p>
      <div class="modal-header">
        <p>Cấp dưới</p>
      </div>
      <div class="modal-content">
        <table class="wp-list-table widefat fixed striped table-view-list users">
          <thead>
            <tr>
              <th>Tên cấp dưới</th>
              <th>Tổng hoa hồng <span class="d-none"><br /> sum (commision status 1 (<b style="color: red;">USER CON</b>))</span></th>
              <th>Tổng doanh thu <span class="d-none"><br /> sum (total_order status 1 (<b style="color: red;">USER CON</b>))</span></th>
              <th>Sản phẩm</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($childUser) === 0) { ?>
              <tr>
                <td>Không có cấp dưới</td>
              </tr>
              <?php } else {
              foreach ($childUser as $child) {
                $childCommissions = $child['commission'];
                $childRevenue = $child['total_order'];
                $childProductId = $child['product_id'];
                $childProductName = $child['post_title'];
              ?>
                <tr>
                  <td><?php echo $child['user_nicename'] . ' - ' . $child['user_login']; ?></td>
                  <td><?php echo $childCommissions; ?></td>
                  <td><?php echo $childRevenue; ?></td>
                  <td><a href="/wp-admin/post.php?post=<?php echo $childProductId; ?>&action=edit"><?php echo $childProductName; ?></a></td>
                </tr>
            <?php }
            } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php } ?>