jQuery(document).ready(function ($) {
  let storeCity = localStorage.getItem("city") || [];
  jQuery(".select2-container").select2();

  if (storeCity.length == 0) {
    jQuery.get("/wp-json/v1/city", function (data, status) {
      if (data.data) {
        storeCity = data.data;
        localStorage.setItem("city", JSON.stringify(data.data));
      }
    });
  } else {
    storeCity = JSON.parse(storeCity);
  }

  // Thêm dòng mới
  $("#add-store-address").on("click", function () {
    let Id = randString();

    let html = ` <div class="ntco_option_col status_1">
                                <div class="ntco_option_box"> <table class=" tableAddress ntco_hubs_table widefat" >
                    <tbody>
                      <tr>
                       <th scope="row" class="titledesc">
								<label for="woocommerce_default_country">Địa chỉ quán <span class="woocommerce-help-tip" tabindex="0" ></span></label>
							</th>
              <td> <input class="form-control" name="address[]" type="text"></input> </td>
                      </tr>  
                       
                    `;
    html += addCity(storeCity);
    html += addQuan();
    html += addPhuong();

    html += `
     <tr>
                       <th scope="row" class="titledesc">
								<label for="woocommerce_default_country">Trạng thái <span class="woocommerce-help-tip" tabindex="0" ></span></label>
							</th>
              <td> <select name="status[]" style="" data-placeholder="Chọn trạng thái"  tabindex="-1" aria-hidden="true">
                                            <option value="1"  >Active</option>
                                            <option value="2"  >Deactive</option>
                                        </select>  </td>
                      </tr>  
      <tr>
        <td><button type="button" class="remove-address button">Xóa</button></td>
      </tr>
    </tbody>
                </table> </div></div>`;

    $("#store-locations-table").append(html);
    jQuery(".select2-container").select2();
  });
});
// Xóa dòng hiện tại
jQuery(document).on("click", ".remove-address", function () {
  jQuery(this).closest(".tableAddress").remove();
});

// Lưu địa chỉ
jQuery(document).on("click", ".remove-address", function () {
  jQuery(this).closest(".tableAddress").remove();
});
function randString() {
  return Date.now();
}
function addCity(storeCity, Id) {
  let html = `<tr>
  <th scope="row" class="titledesc">
								<label for="woocommerce_default_country">Quốc gia / Thành phố <span class="woocommerce-help-tip" tabindex="0" aria-label="Quốc gia và thành phố nơi trụ sở cửa hàng của bạn."></span></label>
							</th>
  <td><select name="city[]" style="" data-placeholder="Chọn khu vực…" aria-label="Quốc gia/Khu vực" class="select2-container changeCity" tabindex="-1" aria-hidden="true">`;
  html += `<option></option>`;

  jQuery(storeCity).each(function (index, item) {
    html += `<option  value="${item.id}" > ${item.name} </option>`;
  });

  html += ` </select></td>
      </tr>`;
  return html;
}

jQuery(document).on("change", ".changeCity", function () {
  let element = jQuery(this).closest(".tableAddress");
  let value = jQuery(this).val();
  //loadquan
  jQuery(element).find(".quan").replaceWith(addQuan());
  jQuery(element).find(".phuong").replaceWith(addPhuong());

  jQuery.post("/wp-json/v1/quan", { parent: value }, function (data, status) {
    if (data.status == "success") {
      let htmlQuan = addQuan(data.data);
      console.log(htmlQuan);

      jQuery(element).find(".quan").replaceWith(htmlQuan);
      jQuery(".select2-container").select2();
    }
  });
});
jQuery(document).on("change", ".changeQuan", function () {
  let element = jQuery(this).closest(".tableAddress");
  let value = jQuery(this).val();
  jQuery(element).find(".phuong").replaceWith(addPhuong());

  //loadquan
  jQuery.post("/wp-json/v1/phuong", { parent: value }, function (data, status) {
    if (data.status == "success") {
      let htmlPhuong = addPhuong(data.data);
      jQuery(element).find(".phuong").replaceWith(htmlPhuong);
      jQuery(".select2-container").select2();
    }
  });
});
function addQuan(quan = []) {
  let html = `<tr class="quan">
  <th scope="row" class="titledesc">
								<label for="woocommerce_default_country"> Quận <span class="woocommerce-help-tip" tabindex="0" aria-label="Quốc gia và thành phố nơi trụ sở cửa hàng của bạn."></span></label>
							</th>
  <td><select name="quan[]" style="" data-placeholder="Chọn Quận…" aria-label="Quận" class="select2-container  changeQuan" tabindex="-1" aria-hidden="true">`;
  html += `<option></option>`;

  jQuery(quan).each(function (index, item) {
    html += `<option  value="${item.id}" > ${item.name} </option>`;
  });

  html += ` </select></td>
      </tr>`;
  return html;
}
function addPhuong(phuong = []) {
  let html = `<tr class="phuong">
  <th scope="row" class="titledesc">
								<label for="woocommerce_default_country">Phường <span class="woocommerce-help-tip" tabindex="0" aria-label="Quốc gia và thành phố nơi trụ sở cửa hàng của bạn."></span></label>
							</th>
  <td><select name="phuong[]" style="" data-placeholder="Chọn Phường…" aria-label="Phường" class="select2-container  " tabindex="-1" aria-hidden="true">`;
  html += `<option></option>`;

  jQuery(phuong).each(function (index, item) {
    html += `<option  value="${item.id}" > ${item.name} </option>`;
  });

  html += ` </select></td>
      </tr>`;
  return html;
}
