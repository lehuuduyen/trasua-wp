<?php
  global $wpdb;
  $successMessage = '';
  $errorMessage = '';
  $tableUser = $wpdb->prefix . 'users';
  $tableShareLink = $wpdb->prefix . 'woo_history_share_link';
  $tableUserCommission = $wpdb->prefix . 'woo_history_user_commission';
  $tablePost = $wpdb->prefix . 'posts';
  $status = [
    'PURCHASE' => 1,
    'USE_POINT' => 2,
    'CANCEL' => 5,
    'USE_POINT_IN_PROCESS' => 4,
  ];

  $usersDisplay = [];
  $users = $wpdb->get_results( 'SELECT * FROM ' . $tableUser . ' WHERE user_login != "77777777" ORDER BY id ASC', ARRAY_A );
  $userCommissions = $wpdb->get_results( 'SELECT * FROM ' . $tableUserCommission . ' ORDER BY id ASC', ARRAY_A );

  if (isset($_GET['searchUser']) && isset($_GET['username'])) {
    $usersSearch = $wpdb->get_results( 'SELECT * FROM ' . $tableUser . ' WHERE user_login != "77777777" && (user_login LIKE "%' . $_GET['username'] . '%" OR user_nicename LIKE "%' . $_GET['username'] . '%" ) ORDER BY id ASC', ARRAY_A );
    $usersDisplay = $usersSearch;
  } else {
    $usersDisplay = $users;
  }

  $currentPage = (! empty( $_GET['paged'] )) && ($_GET['tab'] == 'setting1') ? (int) $_GET['paged'] : 1;
  $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
  $stt = $paged - 1;
  $total = count( $usersDisplay );
  $perPage = 10;                                                                                                                                                   ;
  $totalPages = ceil($total/ $perPage);
  $currentPage = max($currentPage, 1);
  $currentPage = min($currentPage, $totalPages);
  $offset = ($currentPage - 1) * $perPage;
  if ($offset < 0) $offset = 0;
  $usersDisplay = array_slice($usersDisplay, $offset, $perPage);

  if (isset($_POST['saveSetting'])) {
    $wpAffliate = get_option('woo_aff_setting');
    if ($wpAffliate) {
      update_option('woo_aff_setting',$_POST['woo_aff_setting']);
    } else {
      add_option('woo_aff_setting',$_POST['woo_aff_setting']);
    }
    $successMessage = 'Lưu cài đặt thành công';
  }
  if (isset($_POST['saveSettingReward'])) {
    $wpAffliate = get_option('reward_policy');
    if ($wpAffliate) {
      update_option('reward_policy',$_POST['reward_policy']);
    } else {
      add_option('reward_policy',$_POST['reward_policy']);
    }
    $successMessage = 'Lưu cài đặt thành công';
  }
  if (isset($_POST['saveSettingLevel2'])) {
    $wpAffliateLevel2 = get_option('woo_aff_setting_cap2');
    if ($wpAffliateLevel2) {
      update_option('woo_aff_setting_cap2',$_POST['woo_aff_setting_cap2']);
    } else {
      add_option('woo_aff_setting_cap2',$_POST['woo_aff_setting_cap2']);
    }
    $successMessage = 'Lưu cài đặt thành công';
  }

  if (isset($_POST['updateStatus'])) {
    $update = $wpdb->update($tableUserCommission, ['status' => $_POST['status']], ['id' => $_POST['userCommissionId']]);
    $successMessage = 'Cập nhật trạng thái thành công';
  }
?>

<div class="wrap">
  <h1>Hoa Hồng</h1>
  <hr />
  <?php if ($successMessage) { ?>
    <div id="message" class="success-message">
      <p><?php echo $successMessage; ?></p>
      <button id="remove-message" type="button"></button>
    </div>
  <?php } ?>
  <ul class="nav-tabs-affliate">
    <li id="tabSetting1" class="active" onclick="changeUrl(1)">
      <a href="#tab-setting-1-content">Danh sách thành viên</a>
    </li>
    <li id="tabSetting2" onclick="changeUrl(2)">
      <a href="#tab-setting-2-content">Cài đặt tỉ lệ hoa hồng</a>
    </li>
    <li id="tabSetting3" onclick="changeUrl(3)">
      <a href="#tab-setting-3-content">Yêu cầu rút</a>
    </li>
   
  </ul>
  <div class="tab-content">
    <div id="tab-setting-1-content" class="tab-pane-affliate active">
      <?php require_once(dirname(__FILE__) . '/user-list.php'); ?>
    </div>
    <div id="tab-setting-2-content" class="tab-pane-affliate">
      <form action="?page=hoa-hong&paged=1&tab=setting2" method="POST">
        <h4>Cài đặt cấp 1</h4>
        <input type="number" max="100000000" class="regular-text" name="woo_aff_setting" value="<?php echo get_option('woo_aff_setting'); ?>" />
        <button type="submit" class="button button-primary" name="saveSetting">Lưu lại</button>
      </form>
      <form action="?page=hoa-hong&paged=1&tab=setting2" method="POST">
        <h4>Chính sách nhận thưởng</h4>
        <input type="text"  class="regular-text" name="reward_policy" value="<?php echo get_option('reward_policy'); ?>" />
        <button type="submit" class="button button-primary" name="saveSettingReward">Lưu lại</button>
      </form>
    </div>
  
    <div id="tab-setting-3-content" class="tab-pane-affliate">
      <?php require_once(dirname(__FILE__) . '/transfer.php'); ?>
    </div>
    <div id="tab-setting-4-content" class="tab-pane-affliate">
      <?php require_once(dirname(__FILE__) . '/revenue-statistics.php'); ?>
    </div>
  </div>
  <div class="overlay d-none"></div>
  <?php require_once(dirname(__FILE__) . '/child-user-modal.php'); ?>
</div>
