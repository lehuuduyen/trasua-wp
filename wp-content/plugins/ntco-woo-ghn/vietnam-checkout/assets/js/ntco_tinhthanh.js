function copyToClipboard(element) {
  var tempInput = jQuery('<input>')
  jQuery('body').append(tempInput)
  jQuery(element).addClass('copying')
  setTimeout(function () {
    jQuery(element).removeClass('copying')
  }, 300)
  tempInput.val(jQuery(element).html()).select()
  document.execCommand('copy')
  tempInput.remove()
}

; (function ($) {

  ; ('use strict')
  $(document).ready(function () {
    let jsonAddressData = false,
      cityData = {},
      wardData = {}

    let localStorageHandler = (function () {
      let storage = {},
        citiesStorageKey = 'cities_' + vncheckout_array.vnaddress_db_version
      localStorage.setItem(
        citiesStorageKey,
        localStorage.getItem(citiesStorageKey) ? localStorage.getItem(citiesStorageKey) : {}
      )
      let saveCities = function (data) {
        localStorage.setItem(citiesStorageKey, JSON.stringify(data))
      },
        loadCities = function () {
          try {
            let data = JSON.parse(localStorage.getItem(citiesStorageKey))
            if (data == null) {
              return {}
            }
            return data
          } catch (error) {
            return {}
          }
        },
        addCity = function (state, cities) {
          let allCities = loadCities()
          return (allCities[state] = cities), saveCities(allCities), true
        },
        getCity = function (state) {
          let allCities = loadCities()
          if (
            allCities &&
            typeof allCities[state] != 'undefined' &&
            !$.isEmptyObject(allCities[state]) &&
            allCities[state]
          ) {
            return allCities[state]
          }
          return false
        },
        wardsStorageKey = 'ward_' + vncheckout_array.vnaddress_db_version
      localStorage.setItem(
        wardsStorageKey,
        localStorage.getItem(wardsStorageKey) ? localStorage.getItem(wardsStorageKey) : {}
      )
      let saveWards = function (data) {
        localStorage.setItem(wardsStorageKey, JSON.stringify(data))
      },
        loadWards = function () {
          try {
            let data = JSON.parse(localStorage.getItem(wardsStorageKey))
            if (data == null) {
              return {}
            }
            return data
          } catch (error) {
            return {}
          }
        },
        addWard = function (city, wards) {
          let allWards = loadWards()
          return (allWards[city] = wards), saveWards(allWards), true
        },
        getWard = function (city) {
          let allWards = loadWards()
          if (
            allWards &&
            typeof allWards[city] != 'undefined' &&
            !$.isEmptyObject(allWards[city]) &&
            allWards[city]
          ) {
            return allWards[city]
          }
          return false
        }
      return (
        (storage.getAllCity = loadCities),
        (storage.addCity = addCity),
        (storage.getCity = getCity),
        (storage.getAllWard = loadWards),
        (storage.addWard = addWard),
        (storage.getWard = getWard),
        storage
      )
    })()
    function initAddressFields() {
      $(document.body).trigger('vn_address_init')
      $(document.body).on(
        'country_to_state_changing',
        function (event, country, wrapper) {
          wrapper
            .find(
              '#billing_state, #shipping_state, #calc_shipping_state, #billing_city, #shipping_city, #calc_shipping_city'
            )
            .trigger('change')
        }
      )
      $(document.body).on(
        'state_to_city_changed',
        function (event, country, state, wrapper) {
          wrapper.find('#billing_city').trigger('change')
        }
      )
      $(document.body).on(
        'state_to_city_shipping_changed',
        function (event, country, state, wrapper) {
          wrapper.find('#shipping_city').trigger('change')
        }
      )
      $(document.body).on(
        'change refresh',
        '#billing_state, #calc_shipping_state',
        function () {
          let fieldsContainer =
            '.woocommerce-billing-fields,.woocommerce-shipping-fields,.woocommerce-address-fields,.woocommerce-shipping-calculator',
            closestContainer = $(this).closest(fieldsContainer)
          !closestContainer.length &&
            (closestContainer = $(this).closest('.form-row').parent())
          let countryField = closestContainer
            .find('#billing_country, #calc_shipping_country')
            .val(),
            stateField = closestContainer.find('#billing_state, #calc_shipping_state'),
            stateValue = stateField.val(),
            cityField = closestContainer.find('#billing_city, #calc_shipping_city'),
            cityFieldRow = cityField.closest('.form-row'),
            cityNameAttr = cityField.attr('name'),
            cityIdAttr = cityField.attr('id'),
            cityValue = cityField.val(),
            cityDataValue = cityField.attr('data-value'),
            cityPlaceholder =
              cityField.attr('placeholder') ||
              cityField.attr('data-placeholder') ||
              '',
            citySelectElement
          if (!cityValue) {
            cityValue = cityDataValue
          }
          if (countryField == 'VN') {
            let cityPlaceholderOption = $('<option value=""></option>').text(
              vncheckout_array.placeholder_city_text
            )
            !cityPlaceholder && (cityPlaceholder = vncheckout_array.placeholder_city_text)
            cityField.is('input') &&
              ((citySelectElement = $('<select></select>')
                .prop('id', cityIdAttr)
                .prop('name', cityNameAttr)
                .data('placeholder', cityPlaceholder)
                .addClass('state_select')),
                cityField.replaceWith(citySelectElement),
                (cityField = closestContainer.find(
                  '#billing_city, #calc_shipping_city'
                )))
            cityField.attr('data-value', cityValue)
            cityField.empty().append(cityPlaceholderOption)
            if (jsonAddressData) {
              cityData =
                typeof jsonAddressData[stateValue] != 'undefined'
                  ? jsonAddressData[stateValue]
                  : ''
              if (!cityData) {
                cityData = localStorageHandler.getCity(stateValue)
              }
            } else {
              cityData = localStorageHandler.getCity(stateValue)
            }
            if (cityData) {
              $.each(cityData, function (index, city) {
                let cityOption = $('<option></option>')
                  .prop('value', city.maqh)
                  .text(city.name)
                cityField.append(cityOption)
              })
              cityField.val(cityValue)
              cityField.show().selectWoo().hide().trigger('change')
            } else {
              stateValue
                ? (pendingAjax && pendingAjax.abort(),
                  (pendingAjax = $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: vncheckout_array.get_address,
                    data: {
                      action: 'load_diagioihanhchinh',
                      matp: stateValue,
                    },
                    context: this,
                    beforeSend: function () {
                      cityFieldRow.addClass('ntco_loading')
                    },
                    success: function (response) {
                      if (response.success) {

                        let cityList = response.data
                        localStorageHandler.addCity(stateValue, cityList)
                        $.each(
                          cityList,
                          function (index, city) {
                            let cityOption = $('<option></option>')
                              .prop('value', city.maqh)
                              .text(city.name)
                            cityField.append(cityOption)
                          }
                        )
                        cityField.val(cityValue)
                        cityField
                          .show()
                          .selectWoo()
                          .hide()
                          .trigger('change')

                      }
                      cityFieldRow.removeClass('ntco_loading')
                      pendingAjax = null
                    },
                    error: function (xhr, status, error) {
                      pendingAjax = null
                      cityFieldRow.removeClass('ntco_loading')
                    },
                  })))
                : (pendingAjax = null)
            }
            $(document.body).trigger('state_to_city_changed', [
              countryField,
              cityValue,
              closestContainer,
            ])
          } else {
            cityField.is('select, input[type="hidden"]') &&
              ((citySelectElement = $('<input type="text" />')
                .prop('id', cityIdAttr)
                .prop('name', cityNameAttr)
                .prop('placeholder', cityPlaceholder)
                .prop('value', cityValue)
                .addClass('input-text')),
                cityFieldRow.show().find('.select2-container').remove(),
                citySelectElement.val(cityValue),
                cityField.replaceWith(citySelectElement),
                $(document.body).trigger('state_to_city_changed', [
                  countryField,
                  cityValue,
                  closestContainer,
                ]))
          }
          $(document.body).trigger('state_to_city_changing', [
            countryField,
            cityValue,
            closestContainer,
          ])
        }
      )
      $(document.body).on(
        'change refresh',
        '#shipping_state',
        function () {
          let fieldsContainer =
            '.woocommerce-billing-fields,.woocommerce-shipping-fields,.woocommerce-address-fields,.woocommerce-shipping-calculator',
            closestContainer = $(this).closest(fieldsContainer)
          !closestContainer.length &&
            (closestContainer = $(this).closest('.form-row').parent())
          let countryField = closestContainer
            .find('#shipping_country, #calc_shipping_country')
            .val(),
            stateField = closestContainer.find('#shipping_state'),
            stateValue = stateField.val(),
            cityField = closestContainer.find('#shipping_city'),
            cityFieldRow = cityField.closest('.form-row'),
            cityNameAttr = cityField.attr('name'),
            cityIdAttr = cityField.attr('id'),
            cityValue = cityField.val(),
            cityDataValue = cityField.attr('data-value'),
            cityPlaceholder =
              cityField.attr('placeholder') ||
              cityField.attr('data-placeholder') ||
              '',
            citySelectElement
          if (!cityValue) {
            cityValue = cityDataValue
          }
          if (countryField == 'VN') {
            let cityPlaceholderOption = $('<option value=""></option>').text(
              vncheckout_array.placeholder_city_text
            )
            !cityPlaceholder && (cityPlaceholder = vncheckout_array.placeholder_city_text)
            cityField.is('input') &&
              ((citySelectElement = $('<select></select>')
                .prop('id', cityIdAttr)
                .prop('name', cityNameAttr)
                .data('placeholder', cityPlaceholder)
                .addClass('state_select')),
                cityField.replaceWith(citySelectElement),
                (cityField = closestContainer.find('#shipping_city')))
            cityField.attr('data-value', cityValue)
            cityField.empty().append(cityPlaceholderOption)
            if (jsonAddressData) {
              cityData =
                typeof jsonAddressData[stateValue] != 'undefined'
                  ? jsonAddressData[stateValue]
                  : ''
              if (!cityData) {
                cityData = localStorageHandler.getCity(stateValue)
              }
            } else {
              cityData = localStorageHandler.getCity(stateValue)
            }
            if (cityData) {
              $.each(cityData, function (index, city) {
                let cityOption = $('<option></option>')
                  .prop('value', city.maqh)
                  .text(city.name)
                cityField.append(cityOption)
              })
              cityField.val(cityValue)
              cityField.show().selectWoo().hide().trigger('change')
            } else {
              if (stateValue) {
                shippingAjax && shippingAjax.abort()
                shippingAjax = $.ajax({
                  type: 'post',
                  dataType: 'json',
                  url: vncheckout_array.get_address,
                  data: {
                    action: 'load_diagioihanhchinh',
                    matp: stateValue,
                  },
                  context: this,
                  beforeSend: function () {
                    cityFieldRow.addClass('ntco_loading')
                  },
                  success: function (response) {
                    if (response.success) {
                      let cityList = response.data
                      localStorageHandler.addCity(stateValue, cityList)
                      $.each(
                        cityList,
                        function (index, city) {
                          let cityOption = $('<option></option>')
                            .prop('value', city.maqh)
                            .text(city.name)
                          cityField.append(cityOption)
                        }
                      )
                      cityField.val(cityValue)
                      cityField.show().selectWoo().hide().trigger('change')
                    }
                    cityFieldRow.removeClass('ntco_loading')
                    shippingAjax = null
                  },
                  error: function (xhr, status, error) {
                    shippingAjax = null
                    cityFieldRow.removeClass('ntco_loading')
                  },
                })
              } else {
                shippingAjax = null
              }
            }
            $(document.body).trigger('state_to_city_shipping_changed', [
              countryField,
              cityValue,
              closestContainer,
            ])
          } else {
            cityField.is('select, input[type="hidden"]') &&
              ((citySelectElement = $('<input type="text" />')
                .prop('id', cityIdAttr)
                .prop('name', cityNameAttr)
                .prop('placeholder', cityPlaceholder)
                .prop('value', cityValue)
                .addClass('input-text')),
                cityFieldRow.show().find('.select2-container').remove(),
                citySelectElement.val(cityValue),
                cityField.replaceWith(citySelectElement),
                $(document.body).trigger('state_to_city_shipping_changed', [
                  countryField,
                  cityValue,
                  closestContainer
                ]))
          }
          $(document.body).trigger('state_to_city_shipping_changing', [
            countryField,
            cityValue,
            closestContainer
          ])
        }
      )
      $(document.body).on(
        'change refresh',
        '#billing_city',
        function () {
          let fieldsContainer =
            '.woocommerce-billing-fields,.woocommerce-shipping-fields,.woocommerce-address-fields,.woocommerce-shipping-calculator',
            closestContainer = $(this).closest(fieldsContainer)
          !closestContainer.length &&
            (closestContainer = $(this).closest('.form-row').parent())
          let countryField = closestContainer
            .find('#billing_country, #calc_shipping_country')
            .val(),
            stateField = closestContainer.find('#billing_state, #calc_shipping_state'),
            stateValue = stateField.val(),
            cityField = closestContainer.find('#billing_city'),
            cityValue = cityField.val(),
            address1Field = closestContainer.find('#billing_address_1_field'),
            address2Field = closestContainer.find('#billing_address_2'),
            address2FieldRow = address2Field.closest('.form-row'),
            address2NameAttr = address2Field.attr('name'),
            address2IdAttr = address2Field.attr('id'),
            address2Value = address2Field.val(),
            address2DataValue = address2Field.attr('data-value'),
            address2Placeholder =
              address2Field.attr('placeholder') ||
              address2Field.attr('data-placeholder') ||
              '',
            wardSelectElement
          if (!address2Value) {
            address2Value = address2DataValue
          }
          if (countryField == 'VN') {
            let wardPlaceholderOption = $('<option value=""></option>').text(
              vncheckout_array.placeholder_ward_text
            )
            !address2Placeholder && (address2Placeholder = vncheckout_array.placeholder_ward_text)
            address2Field.is('input') &&
              ((wardSelectElement = $('<select></select>')
                .prop('id', address2IdAttr)
                .prop('name', address2NameAttr)
                .data('placeholder', address2Placeholder)
                .addClass('state_select')),
                address2Field.replaceWith(wardSelectElement),
                (address2Field = closestContainer.find('#billing_address_2')))
            address2Field.attr('data-value', address2Value)
            address2Field.empty().append(wardPlaceholderOption)
            if (
              jsonAddressData &&
              jsonAddressData[stateValue] &&
              jsonAddressData[stateValue][cityValue]
            ) {
              wardData =
                typeof jsonAddressData[stateValue][cityValue].wards != 'undefined'
                  ? jsonAddressData[stateValue][cityValue].wards
                  : ''
              if (!wardData) {
                wardData = localStorageHandler.getWard(cityValue)
              }
            } else {
              wardData = localStorageHandler.getWard(cityValue)
            }
            if (wardData) {
              address2Field.closest('.form-row').removeClass('vn_address_hide')
              address1Field.removeClass('vn_address_full')
              Object.keys(wardData).length > 0
                ? $.each(wardData, function (index, ward) {
                  let wardOption = $('<option></option>')
                    .prop('value', ward.xaid)
                    .text(ward.name)
                  address2Field.append(wardOption)
                })
                : (address2Field.closest('.form-row').addClass('vn_address_hide'),
                  address1Field.addClass('vn_address_full'))
              address2Field.val(address2Value)
              address2Field.show().selectWoo().hide().trigger('change')
            } else {
              cityValue
                ? (billingAjax && billingAjax.abort(),
                  (billingAjax = $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: vncheckout_array.get_address,
                    data: {
                      action: 'load_diagioihanhchinh',
                      maqh: cityValue,
                    },
                    context: this,
                    beforeSend: function () {
                      address2FieldRow.addClass('ntco_loading')
                    },
                    success: function (response) {
                      if (response.success) {
                        let wardList = response.data
                        localStorageHandler.addWard(cityValue, wardList)
                        $.each(
                          wardList,
                          function (index, ward) {
                            let wardOption = $('<option></option>')
                              .prop('value', ward.xaid)
                              .text(ward.name)
                            address2Field.append(wardOption)
                          }
                        )
                        address2Field.val(address2Value)
                        address2Field.show().selectWoo().hide().trigger('change')
                      }
                      address2FieldRow.removeClass('ntco_loading')
                      billingAjax = null
                    },
                    error: function (xhr, status, error) {
                      billingAjax = null
                      address2FieldRow.removeClass('ntco_loading')
                    },
                  })))
                : (billingAjax = null)
            }
            $(document.body).trigger('city_to_ward_changed', [
              countryField,
              cityValue,
              address2Value,
              closestContainer,
            ])
          } else {
            address2Field.is('select, input[type="hidden"]') &&
              ((wardSelectElement = $('<input type="text" />')
                .prop('id', address2IdAttr)
                .prop('name', address2NameAttr)
                .prop('placeholder', address2Placeholder)
                .prop('value', address2Value)
                .addClass('input-text')),
                address2FieldRow.show().find('.select2-container').remove(),
                wardSelectElement.val(address2Value),
                address2Field.replaceWith(wardSelectElement),
                $(document.body).trigger('city_to_ward_changed', [
                  countryField,
                  cityValue,
                  address2Value,
                  closestContainer,
                ]))
          }
          $(document.body).trigger('city_to_ward_changing', [
            countryField,
            cityValue,
            address2Value,
            closestContainer,
          ])
        }
      )
      $(document.body).on(
        'change refresh',
        '#shipping_city',
        function () {
          let fieldsContainer =
            '.woocommerce-billing-fields,.woocommerce-shipping-fields,.woocommerce-address-fields,.woocommerce-shipping-calculator',
            closestContainer = $(this).closest(fieldsContainer)
          !closestContainer.length &&
            (closestContainer = $(this).closest('.form-row').parent())
          let countryField = closestContainer
            .find('#shipping_country, #calc_shipping_country')
            .val(),
            stateField = closestContainer.find('#shipping_state, #calc_shipping_state'),
            stateValue = stateField.val(),
            cityField = closestContainer.find('#shipping_city'),
            cityValue = cityField.val(),
            address1Field = closestContainer.find('#shipping_address_1_field'),
            address2Field = closestContainer.find('#shipping_address_2'),
            address2FieldRow = address2Field.closest('.form-row'),
            address2NameAttr = address2Field.attr('name'),
            address2IdAttr = address2Field.attr('id'),
            address2Value = address2Field.val(),
            address2DataValue = address2Field.attr('data-value'),
            address2Placeholder =
              address2Field.attr('placeholder') ||
              address2Field.attr('data-placeholder') ||
              '',
            wardSelectElement
          if (!address2Value) {
            address2Value = address2DataValue
          }
          if (countryField == 'VN') {
            let wardPlaceholderOption = $('<option value=""></option>').text(
              vncheckout_array.placeholder_ward_text
            )
            !address2Placeholder && (address2Placeholder = vncheckout_array.placeholder_ward_text)
            address2Field.is('input') &&
              ((wardSelectElement = $('<select></select>')
                .prop('id', address2IdAttr)
                .prop('name', address2NameAttr)
                .data('placeholder', address2Placeholder)
                .addClass('state_select')),
                address2Field.replaceWith(wardSelectElement),
                (address2Field = closestContainer.find('#shipping_address_2')))
            address2Field.attr('data-value', address2Value)
            address2Field.empty().append(wardPlaceholderOption)
            if (
              jsonAddressData &&
              jsonAddressData[stateValue] &&
              jsonAddressData[stateValue][cityValue]
            ) {
              wardData =
                typeof jsonAddressData[stateValue][cityValue].wards != 'undefined'
                  ? jsonAddressData[stateValue][cityValue].wards
                  : ''
              if (!wardData) {
                wardData = localStorageHandler.getWard(cityValue)
              }
            } else {
              wardData = localStorageHandler.getWard(cityValue)
            }
            if (wardData) {
              address2Field.closest('.form-row').removeClass('vn_address_hide')
              address1Field.removeClass('vn_address_full')
              Object.keys(wardData).length > 0
                ? $.each(wardData, function (index, ward) {
                  let wardOption = $('<option></option>')
                    .prop('value', ward.xaid)
                    .text(ward.name)
                  address2Field.append(wardOption)
                })
                : (address2Field.closest('.form-row').addClass('vn_address_hide'),
                  address1Field.addClass('vn_address_full'))
              address2Field.val(address2Value)
              address2Field.show().selectWoo().hide().trigger('change')
            } else {
              cityValue
                ? (wardAjax && wardAjax.abort(),
                  (wardAjax = $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: vncheckout_array.get_address,
                    data: {
                      action: 'load_diagioihanhchinh',
                      maqh: cityValue,
                    },
                    context: this,
                    beforeSend: function () {
                      address2FieldRow.addClass('ntco_loading')
                    },
                    success: function (response) {
                      if (response.success) {
                        let wardList = response.data
                        localStorageHandler.addWard(cityValue, wardList)
                        $.each(
                          wardList,
                          function (index, ward) {
                            let wardOption = $('<option></option>')
                              .prop('value', ward.xaid)
                              .text(ward.name)
                            address2Field.append(wardOption)
                          }
                        )
                        address2Field.val(address2Value)
                        address2Field.show().selectWoo().hide().trigger('change')
                      }
                      address2FieldRow.removeClass('ntco_loading')
                      wardAjax = null
                    },
                    error: function (xhr, status, error) {
                      wardAjax = null
                      address2FieldRow.removeClass('ntco_loading')
                    },
                  })))
                : (wardAjax = null)
            }
            $(document.body).trigger('city_to_ward_shipping_changed', [
              countryField,
              cityValue,
              address2Value,
              closestContainer,
            ])
          } else {
            address2Field.is('select, input[type="hidden"]') &&
              ((wardSelectElement = $('<input type="text" />')
                .prop('id', address2IdAttr)
                .prop('name', address2NameAttr)
                .prop('placeholder', address2Placeholder)
                .prop('value', address2Value)
                .addClass('input-text')),
                address2FieldRow.show().find('.select2-container').remove(),
                wardSelectElement.val(address2Value),
                address2Field.replaceWith(wardSelectElement),
                $(document.body).trigger('city_to_ward_shipping_changed', [
                  countryField,
                  cityValue,
                  address2Value,
                  closestContainer
                ]))
          }
          $(document.body).trigger('city_to_ward_shipping_changing', [
            countryField,
            cityValue,
            address2Value,
            closestContainer,
          ])
        }
      )
      $('#billing_country[type="hidden"]').length > 0 &&
        $('#billing_state, #calc_shipping_state, #billing_city, #calc_shipping_city').trigger('change')
      $('#shipping_country[type="hidden"]').length > 0 &&
        $('#shipping_state, #calc_shipping_state, #shipping_city, #calc_shipping_city').trigger('change')
    }
    function refreshAddressFields() {
      $('#billing_state, #calc_shipping_state, #billing_city, #calc_shipping_city').trigger('change')
      $('#shipping_state, #calc_shipping_state, #shipping_city, #calc_shipping_city').trigger('change')
    }
    vncheckout_array.json_address_enable && vncheckout_array.json_address
      ? $.getJSON(vncheckout_array.json_address, function (data) {
        for (let key in localStorage) {
          (key.startsWith('ward_') || key.startsWith('cities_')) &&
            localStorage.removeItem(key)
        }
        jsonAddressData = data
        initAddressFields()
        refreshAddressFields()
      })
        .fail(function (xhr, status, error) {
          initAddressFields()
          refreshAddressFields()
        })
      : initAddressFields()
    $('#ntco_ghtk_tracking').on('submit', function () {
      var formData = $(this).serialize(),
        trackingForm = $(this).closest('.ntco_ghtk_tracking_form')
      return (
        $.ajax({
          type: 'post',
          dataType: 'json',
          url: vncheckout_array.admin_ajax,
          data: {
            action: 'ghtk_tracking',
            data: formData,
          },
          context: this,
          beforeSend: function () {
            trackingForm.addClass('ntco_loading')
          },
          success: function (response) {
            var data = response.data
            data &&
              $.each(
                data.fragments,
                function (selector, content) {
                  $(selector, trackingForm).html(content)
                }
              )
            trackingForm.removeClass('ntco_loading')
          },
          error: function (xhr, status, error) {
            trackingForm.removeClass('ntco_loading')
          },
        }),
        false
      )
    })
    $('body').on('change', 'input[name=payment_method]', function () {
      (vncheckout_array.enabled_free_shipping || vncheckout_array.has_vtp) &&
        $('form.checkout').trigger('update_checkout')
    })
    if (
      typeof magnificPopup != undefined &&
      $('.get_address_byphone').length > 0
    ) {
      $('.get_address_byphone').magnificPopup({
        type: 'inline',
        midClick: true,
        showCloseBtn: false,
      })
      var addressPopupOpen = false
      $('body').on('click', '.btn_get_address', function () {
        if (addressPopupOpen) {
          return false
        }
        var button = $(this),
          popupContent = $(this).closest('#get_address_content'),
          phoneNumber = $('#sdt_get_address', popupContent).val(),
          messageContainer = $('.get_address_content_mess', popupContent),
          captchaResponse = ''
        if (!phoneNumber || !/^0+(\d{9,10})$/.test(phoneNumber)) {
          return messageContainer.html(vncheckout_array.phone_error), false
        } else {
          messageContainer.html('')
          if ($('#g-recaptcha-response', popupContent).length > 0) {
            captchaResponse = $('#g-recaptcha-response', popupContent).val()
            if (!captchaResponse) {
              return messageContainer.html('Vui lòng nhập mã xác thực.'), false
            }
          }
          $.ajax({
            type: 'post',
            dataType: 'json',
            url: vncheckout_array.admin_ajax,
            data: {
              action: 'get_address_byphone',
              phone: phoneNumber,
              'g-recaptcha-response': captchaResponse,
            },
            context: this,
            beforeSend: function () {
              messageContainer.html(vncheckout_array.loading_text)
              button.addClass('ntco_loading')
              addressPopupOpen = true
            },
            success: function (response) {
              response.success
                ? ($.each(
                  response.data.billing,
                  function (fieldId, fieldValue) {
                    $('#' + fieldId).is('select')
                      ? $('#' + fieldId)
                        .val(fieldValue)
                        .attr('data-value', fieldValue)
                      : $('#' + fieldId)
                        .val(fieldValue)
                        .attr('value', fieldValue)
                  }
                ),
                  $.each(
                    response.data.shipping,
                    function (fieldId, fieldValue) {
                      $('#' + fieldId).is('select')
                        ? $('#' + fieldId)
                          .val(fieldValue)
                          .attr('data-value', fieldValue)
                        : $('#' + fieldId)
                          .val(fieldValue)
                          .attr('value', fieldValue)
                    }
                  ),
                  messageContainer.html(''),
                  $('#billing_state, #shipping_state, #billing_city, #shipping_city').trigger('change'),
                  $.magnificPopup.close())
                : messageContainer.html(vncheckout_array.loadaddress_error)
              button.removeClass('ntco_loading')
              addressPopupOpen = false
            },
            error: function (xhr, status, error) {
              button.removeClass('ntco_loading')
              alert(status)
            },
          })
        }
        return false
      })
      $('body').on('click', '.btn_cancel', function () {
        var popupContent = $(this).closest('#get_address_content'),
          messageContainer = $('.get_address_content_mess', popupContent)
        return (
          popupContent.removeClass('get_address_error'),
          $.magnificPopup.close(),
          (addressPopupOpen = false),
          messageContainer.html(''),
          $('#billing_first_name').length > 0
            ? $('#billing_first_name').focus()
            : $('#billing_last_name').focus(),
          false
        )
      })
    }
  })
})(jQuery)
