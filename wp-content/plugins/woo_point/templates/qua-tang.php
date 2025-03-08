<?php
global $wpdb;
if (isset($_POST['submitPrize'])) {

  $arrayInsert = array(
    'count' => $_POST['count'],
    'name' => $_POST['name'],
    'type' => $_POST['type'],
    'point' => $_POST['point'],
    'status' => $_POST['status'],
    'percent' => $_POST['percent'],
  );
 
  if(!empty($_POST['id'])){
    $wpdb->update("wp_woo_point_prize", $arrayInsert,array('id' => $_POST['id']));
   
  }else{
    $wpdb->insert("wp_woo_point_prize", $arrayInsert);

  }

  $successMessage = 'Thêm hạng thành viên thành công';
}
$listPrize = $wpdb->get_results('SELECT * FROM wp_woo_point_prize', ARRAY_A);

?>

<style></style>

<div style="margin:20px 0">
  <button type="button" class="btn btn-primary" onclick="document.getElementById('exampleModal1').classList.add('show');jQuery(`input[name='id']`).val('') " data-bs-toggle="modal1" data-bs-target="#exampleModal1" data-bs-whatever="@mdo">Thêm</button>

</div>

<div class="modal1" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModal1Label" aria-hidden="true">
  <div class="modal1-dialog">
    <div class="modal1-content">
      <div class="modal1-header">
        <h5 class="modal1-title" id="exampleModal1Label">Tạo quà tặng</h5>
      </div>
      <form method="post" action="" id="store-notification-table">

        <div class="modal1-body">
          <div class="mb-3">
            <label for="recipient-name" class="col-form-label">Quà tặng :</label>
            <input type="text" name="name" class="form-control">
          </div>
          <div class="mb-3">
            <label for="message-text" class="col-form-label">Loại quà tặng:</label>
            <select name="type">
              <option value="1">Mã giảm giá</option>
              <option value="2">Quà tặng</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="recipient-name" class="col-form-label">Max :</label>
            <input type="number" name="count" class="form-control">
          </div>
          <div class="mb-3">
            <label for="recipient-name" class="col-form-label">Phần trăm giảm (nếu chọn mã giảm giá) :</label>
            <input type="number" name="percent" min="0" max="100" class="form-control">
          </div>
          <div class="mb-3">
            <label for="recipient-name" class="col-form-label">Số điểm đổi :</label>
            <input type="number" name="point" class="form-control">
          </div>
          <div class="mb-3">
            <label for="message-text" class="col-form-label">Trạng thái:</label>
            <select name="status">
              <option value="1">Mở</option>
              <option value="2">Đóng</option>
            </select>
          </div>
          <input type="hidden" name="id" >

        </div>
        <div class="modal1-footer">
          <button type="button" onclick="document.getElementById('exampleModal1').classList.remove('show'); " class="btn btn-secondary" data-bs-dismiss="modal1">Close</button>
          <button type="submit" name="submitPrize" class="btn btn-primary">Tạo quà tặng </button>
        </div>
      </form>

    </div>
  </div>
</div>


<table id="prize" class="wp-list-table widefat fixed striped table-view-list users">
  <thead>
    <tr>
      <th>Tên</th>
      <th>Số lượng đổi</th>
      <th>Max</th>
      <th>Loại</th>
      <th>Số điểm đổi</th>
      <th>Trạng thái</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($listPrize as $key => $prize) { ?>
      <tr>
        <td><?php echo $prize['name'] ?></td>
        <td><?php echo $prize['quantity'] ?></td>
        <td><?php echo $prize['count'] ?></td>
        <td><?php echo ($prize['type'] == 1) ? "Mã giảm giá" : "Quà tặng" ?></td>
        <td><?php echo $prize['point'] ?></td>

        <td><?php echo ($prize['status'] == 1) ? "Mở" : "Đóng" ?></td>
        <td><button type="button" class="btn btn-primary"
            data-id="<?= $prize['id'] ?>"
            data-name="<?= $prize['name'] ?>"
            data-count="<?= $prize['count'] ?>"
            data-type="<?= $prize['type'] ?>"
            data-status="<?= $prize['status'] ?>"
            data-max="<?= $prize['max'] ?>"
            data-point="<?= $prize['point'] ?>"
            data-percent="<?= $prize['percent'] ?>"
            onclick="showUpdate(this)">Cập nhật</button></td>

      </tr>
    <?php } ?>
  </tbody>
</table>
<script>
  function showUpdate(_this) {
    document.getElementById('exampleModal1').classList.add('show');
    let name = jQuery(_this).data('name');
    let count = jQuery(_this).data('count');
    let type = jQuery(_this).data('type');
    let status = jQuery(_this).data('status');
    let max = jQuery(_this).data('max');
    let point = jQuery(_this).data('point');
    let percent = jQuery(_this).data('percent');
    let id = jQuery(_this).data('id');
    jQuery("input[name='name']").val(name)
    jQuery("input[name='count']").val(count)
    jQuery("input[name='max']").val(max)
    jQuery("input[name='id']").val(id)
    jQuery("input[name='point']").val(point)
    jQuery("input[name='percent']").val(percent)
    jQuery("select[name='status']").val(status)
    jQuery("select[name='type']").val(type)



    }
  jQuery(document).ready(function($) {

    
    // document.getElementById('exampleModal1').classList.add('show'); 


    $('#prize').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "pageLength": 5
    });



  });
</script>