<div id="modal-add" class="modal d-none">
  <div class="modal-wrapper">
    <p id="modal-close" class="close">✕</p>
    <div class="modal-header">
      <p>Thêm xếp hạng</p>
    </div>
    <form action="" method="POST">
      <div class="modal-content">
        <ul id="modal-steps" class="steps">
          <li id="step-1">
            <p class="step-number button">1</p>
            <span class="check dashicons dashicons-saved"></span>
            <p class="step-title">Thiết lập điểm</p>
          </li>
          <li id="step-2">
            <p class="step-number button">2</p>
            <span class="check dashicons dashicons-saved"></span>
            <p class="step-title">Thiết lập xếp hạng thành viên</p>
          </li>
          <li id="step-3">
            <p class="step-number button">3</p>
            <span class="check dashicons dashicons-saved"></span>
            <p class="step-title">Thiết lập khuyến mãi</p>
          </li>
          <li id="step-4">
            <p class="step-number button">4</p>
            <span class="check dashicons dashicons-saved"></span>
            <p class="step-title">Kiểm tra lại</p>
          </li>
        </ul>
        <div class="step-content content-step-1">
          <div class="step-content-header">
            <h2>Thiết lập giá trị tích điểm</h2>
            <p>Xác định số điểm sẽ được cộng cho khách hàng khi hoàn thành đơn hàng và giá trị quy đổi điểm thành tiền khi thanh toán</p>
          </div>
          <hr />
          <div class="step-content-content flex-center">
            <div>
              <h4>Tỉ lệ tích điểm theo chỉ tiêu</h4>
              <div class="flex-center group-input">
                <div>
                  <p class="required">Số tiền chi tiêu quy đổi ra 1 điểm</p>
                  <input type="text" class="regular-text">
                </div>
                <div>
                  <p>Điểm quy đổi ra tiền</p>
                  <input type="text">
                </div>
              </div>
            </div>
            <div>
              <h4>Tỉ lệ tích điểm theo chỉ tiêu</h4>
              <div class="flex-center group-input">
                <div>
                  <p>Số tiền chi tiêu</p>
                  <input type="text" class="regular-text">
                </div>
                <div>
                  <p class="required">Điểm quy đổi ra tiền</p>
                  <input type="text">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="step-content content-step-2">
          <div class="step-content-header">
            <h2>Thiết lập xếp hạng thành viên</h2>
            <p>Tạo mới hạng thành viên và điều kiện đạt hạng</p>
          </div>
          <hr />
          <div class="step-content-content">
            <button id="add-more-record" type="button" class="button flex-center" style="margin-bottom: 20px;">
              <span class="dashicons dashicons-insert"></span>Thêm
            </button>
            <table class="wp-list-table widefat striped table-view-list">
              <thead>
                <tr>
                  <th>Hình ảnh</th>
                  <th>Xếp hạng</th>
                  <th>Chi tiêu tối thiểu</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody id="table-step-2">
                <tr id="record-step-2-add-1">
                  <td class="flex-center">
                    <button type="button" class="upload-image-button button flex-center">
                      <span class="dashicons dashicons-admin-media"></span>
                      <span>Tải lên</span>
                    </button>
                    <input type="text" class="image-url" hidden name="imageurl[]" />
                    <div class="image-wrapper" style="width: 50px; height: auto;"></div>
                  </td>
                  <td>
                    <p class="required">Rank</p>
                    <input class="rank-name require-field" type="text" name="name[]" placeholder="Vui lòng nhập Rank" />
                    <p class="form-error-text d-none">Đây là trường bắt buộc</p>
                  </td>
                  <td>
                    <p class="required">Chi tiêu tối thiểu</p>
                    <input class="require-field" type="number" name="minimum_spending[]" placeholder="Vui lòng nhập Chi tiêu tối thiểu" />
                    <p class="form-error-text d-none">Đây là trường bắt buộc</p>
                  </td>
                  <td>
                    <span disabled class="button delete delete-add-record">✕</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="step-content content-step-3">
          <div class="step-content-header">
            <h2>Thiết lập ưu đãi hạng thành viên</h2>
            <p>Tạo ưu đãi thành viên để hấp dẫn khách hàng mua hàng</p>
          </div>
          <hr />
          <div class="step-content-content" style="overflow-x:auto;">
            <table class="wp-list-table widefat striped table-view-list">
                <thead>
                  <tr>
                    <th>Xếp hạng</th>
                    <th>Khuyến mãi</th>
                    <th>Giới hạn khuyến mãi</th>
                    <th>Số tiền khuyến mãi tối đa cho một đơn hàng</th>
                  </tr>
                </thead>
                <tbody id="table-step-3">
                  <tr id="record-step-3-add-1">
                    <td>
                      <span class="dashicons dashicons-plus-alt"></span>
                      <span class="show-rank"></span>
                    </td>
                    <td>
                      <p class="required">Khuyến mãi</p>
                      <input class="require-field" type="number" name="price_sale_off[]" placeholder="Vui lòng nhập Khuyến mãi" />
                      <p class="form-error-text d-none">Đây là trường bắt buộc</p>
                    </td>
                    <td>
                      <input type="checkbox" class="is-limit-input" name="is_limit[]" />
                    </td>
                    <td>
                      <p>Không giới hạn số tiền</p>
                      <div class="is-limit-content d-none">
                        <p class="required">Số tiền khuyến mãi tối đa cho một đơn hàng</p>
                        <input class="price-sale-off-max require-field require-field-limit" type="number" name="price_sale_off_max[]" placeholder="Vui lòng nhập Số tiền khuyến mãi tối đa cho một đơn hàng" />
                        <p class="form-error-text d-none">Đây là trường bắt buộc</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
            </table>
          </div>
        </div>
        <div class="step-content content-step-4">
          <div class="step-content-header">
            <h2>Hãy kiểm tra lại thông tin trước khi bấm tạo nhé!</h2>
            <p>Quy tắc tích điểm: <b>Chi tiêu <span id="points_converted_to_money_span_edit_all"><?php echo count($settings) > 0 ? $settings[0]['points_converted_to_money'] : ''; ?>đ = 1 Điểm</b></p>
            <p>Quy tắc đổi điểm: <b>1 điểm = <span id="amount_spent_span_edit_all"><?php echo count($settings) > 0 ? $settings[0]['points_converted_to_money'] : ''; ?>đ</b></p>
          </div>
          <hr />
          <div class="step-content-content" style="overflow-x:auto;">
            <table class="wp-list-table widefat striped table-view-list">
                <thead>
                  <tr>
                    <th>Xếp hạng</th>
                    <th>Chi tiêu tối thiểu</th>
                    <th>Khuyến mãi</th>
                    <th>Số tiền khuyến mãi tối đa cho một đơn hàng</th>
                  </tr>
                </thead>
                <tbody id="table-step-4">
                  <tr id="record-step-4-add-1">
                    <td>
                      <span class="dashicons dashicons-plus-alt"></span>
                      <span class="show-rank-final"></span>
                    </td>
                    <td>
                      <span class="show-minimum-spending-final"></span>
                    </td>
                    <td>
                      <span class="show-price-sale-off-final"></span>
                    </td>
                    <td>
                      <span>Không giới hạn số tiền</span>
                      <div class="is-limit-content-final d-none">
                        <span class="show-price-sale-off-max-final"></span>
                      </div>
                    </td>
                  </tr>
                </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" id="modal-prev" class="button">Quay lại</button>
        <button type="button" id="modal-next-step-2" class="button button-primary">Tiếp theo</button>
        <button type="button" id="modal-next-step-3" class="button button-primary">Tiếp theo</button>
        <button type="submit" id="modal-update" class="button button-primary" name="addRanking">Tạo</button>
      </div>
    </form>
  </div>
</div>
