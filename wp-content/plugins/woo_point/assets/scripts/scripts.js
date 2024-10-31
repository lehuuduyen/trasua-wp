const hiddenClass = 'd-none';
const activeClass = 'active';
let isEditAll = false;
let step = 1;
let stepEditAll = 1;
let ids = [];
let triggerEditAll = 0;

function handleConvertMoney(e) {
  const span = document.getElementById('points_converted_to_money_span');
  span.innerHTML = e.value;

  const spanEditAll = document.getElementById('points_converted_to_money_span_edit_all');
  spanEditAll.innerHTML = e.value;
}

function handleAmountSpent(e) {
  const span = document.getElementById('amount_spent_span');
  span.innerHTML = e.value;

  const spanEditAll = document.getElementById('amount_spent_span_edit_all');
  spanEditAll.innerHTML = e.value;
}

function showEditAll() {
  document.getElementById('button-open-modal-edit-all').style.cssText = 'display: flex';
  document.getElementById('button-open-modal-edit-all').classList.remove(hiddenClass);
  document.getElementById('button-open-modal-add').style.cssText = 'display: flex';
  document.getElementById('button-open-modal-add').classList.remove(hiddenClass);
  document.getElementById('modal-delete-btn').classList.remove(hiddenClass);
}

function hideEditAll() {
  document.getElementById('button-open-modal-edit-all').style.cssText = 'display: none !important';
  document.getElementById('button-open-modal-add').style.cssText = 'display: none !important';
  document.getElementById('modal-delete-btn').classList.add(hiddenClass);
}

function showModal(id) {
  const modalOverlay = document.getElementById('overlay');
  const modal = document.getElementById(id);
  modalOverlay.classList.remove(hiddenClass);
  modal.classList.remove(hiddenClass);
}

function hideModal(id) {
  const modalOverlay = document.getElementById('overlay');
  const modal = document.getElementById(id);
  modalOverlay.classList.add(hiddenClass);
  modal.classList.add(hiddenClass);
  step = 1;
}

function hideModalAll(id) {
  const modalOverlay = document.getElementById('overlay');
  const modal = document.getElementById(id);
  modalOverlay.classList.add(hiddenClass);
  modal.classList.add(hiddenClass);
  stepEditAll = 1;
}

function changeModalContentEdit() {
  for (let i = 1; i <= 4; i++) {
    const contentStep = document.getElementsByClassName(`content-step-${i}-edit`);
    if (i === step) {
      for (let j = 0; j < contentStep.length; j++) {
        contentStep[j].classList.remove(hiddenClass);
      }
    } else {
      for (let j = 0; j < contentStep.length; j++) {
        contentStep[j].classList.add(hiddenClass);
      }
    }
  }
}

function changeModalContentEditAll() {
  for (let i = 1; i <= 4; i++) {
    const contentStep = document.getElementsByClassName(`content-step-${i}-edit-all`);
    if (i === stepEditAll) {
      for (let j = 0; j < contentStep.length; j++) {
        contentStep[j].classList.remove(hiddenClass);
      }
    } else {
      for (let j = 0; j < contentStep.length; j++) {
        contentStep[j].classList.add(hiddenClass);
      }
    }
  }
}

function handleChangeValue(tags, classes) {
  tags.forEach((tag, index) => {
    tag.addEventListener('change', function(e) {
      classes[index].innerHTML = e.target.value;
    });
  });
}

function handleInputChange(modal) {
  if (modal) {
    /* Handle rank name */
    const rankNames = modal.querySelectorAll('input[name="name[]"]');
    const showRanks = modal.querySelectorAll('.show-rank');
    const showRanksFinal = modal.querySelectorAll('.show-rank-final');
    handleChangeValue(rankNames, showRanks);
    handleChangeValue(rankNames, showRanksFinal);

    /* Handle minimum spending */
    const minimumSpending = modal.querySelectorAll('input[name="minimum_spending[]"]');
    const showMinimumSpendingFinal = modal.querySelectorAll('.show-minimum-spending-final');
    handleChangeValue(minimumSpending, showMinimumSpendingFinal);

    /* Handle price sale off */
    const priceSaleOff = modal.querySelectorAll('input[name="price_sale_off[]"]');
    const showPriceSaleOffFinal = modal.querySelectorAll('.show-price-sale-off-final');
    handleChangeValue(priceSaleOff, showPriceSaleOffFinal);

    /* Handle price sale off max */
    const priceSaleOffMax = modal.querySelectorAll('input[name="price_sale_off_max[]"]');
    const showPriceSaleOffMaxFinal = modal.querySelectorAll('.show-price-sale-off-max-final');
    handleChangeValue(priceSaleOffMax, showPriceSaleOffMaxFinal);

    /* Handle limit input */
    const inputs = modal.querySelectorAll('input[name="is_limit[]"]');
    const div = modal.querySelectorAll('.is-limit-content');
    const divFinal = modal.querySelectorAll('.is-limit-content-final');
    const priceSaleOffMaxByClass = modal.querySelectorAll('.price-sale-off-max');
    inputs.forEach((input, index) => {
      input.addEventListener('click', function() {
        if (input.checked) {
          div[index].classList.remove(hiddenClass);
          div[index].previousElementSibling.classList.add(hiddenClass);
          divFinal[index].classList.remove(hiddenClass);
          divFinal[index].previousElementSibling.classList.add(hiddenClass);
        } else {
          priceSaleOffMaxByClass[index].value = '';
          div[index].classList.add(hiddenClass);
          div[index].previousElementSibling.classList.remove(hiddenClass);
          divFinal[index].classList.add(hiddenClass);
          divFinal[index].previousElementSibling.classList.remove(hiddenClass);
        }
      });
    });
  }
}

function openEditModal(id) {
  isEditAll = false;
  step = isEditAll ? 1 : 2;
  const modalOverlay = document.getElementById('overlay');
  const modal = document.getElementById(`modal-edit-ranking-${id}`);
  const modalNextStep2 = document.getElementById(`modal-next-edit-step-2-${id}`);
  const modalNextStep3 = document.getElementById(`modal-next-edit-step-3-${id}`);
  const modalPrev = document.getElementById(`modal-prev-edit-${id}`);
  const modalUpdate = document.getElementById(`modal-update-edit-${id}`);
  const uploadImageBtn = document.getElementById(`upload-image-button-edit-${id}`);
  modalOverlay.classList.remove(hiddenClass);
  modal.classList.remove(hiddenClass);
  modalPrev.classList.add(hiddenClass);
  modalUpdate.classList.add(hiddenClass);
  modalNextStep2.classList.remove(hiddenClass);
  modalNextStep3.classList.add(hiddenClass);

  for (let i = 1; i <= 4; i++) {
    document.getElementById(`step-${i}-edit-${id}`).classList.remove('current');
    document.getElementById(`step-${i}-edit-${id}`).classList.remove('active');
  }

  document.getElementById(`step-${step}-edit-${id}`).classList.add('current');

  for (let i = step - 1; i > 0; i--) {
    document.getElementById(`step-${i}-edit-${id}`).classList.add('active');
  }

  changeModalContentEdit();

  !ids.includes(id) && modalNextStep2.addEventListener('click', function() {
    let checkPassStep2 = 0;
    if (step === 2) {
      const contentStep2 = document.querySelector('.content-step-2-edit');
      const requireInputStep2 = contentStep2.querySelectorAll('.require-field');

      requireInputStep2.forEach((input) => {
        if (input.value === '' || input.value < 0) {
          input.nextElementSibling.classList.remove(hiddenClass);
        } else {
          checkPassStep2++;
          input.nextElementSibling.classList.add(hiddenClass);
        }
      });

      if (checkPassStep2 === requireInputStep2.length) {
        nextStep();
      }
    }
  });

  !ids.includes(id) && modalNextStep3.addEventListener('click', function() {
    let checkPassStep3 = 0;
    if (step === 3) {
      const contentStep3 = document.querySelector('.content-step-3-edit');
      const requireInputStep3 = contentStep3.querySelectorAll('.require-field');
      const limitInput = contentStep3.querySelector('.is-limit-input');
      
      requireInputStep3.forEach((input) => {
        if (input.value === '' || input.value < 0) {
          input.nextElementSibling.classList.remove(hiddenClass);
        } else {
          checkPassStep3++;
          input.nextElementSibling.classList.add(hiddenClass);
        }
      });

      if (checkPassStep3 === requireInputStep3.length || (checkPassStep3 === 1 && limitInput.checked === false)) {
        nextStep();
      }
    }
  });

  function nextStep() {
    step++;
    changeModalContentEdit();
    modalPrev.classList.remove(hiddenClass);
    document.getElementById(`step-${step}-edit-${id}`).classList.add('current');

    for (let i = step - 1; i > 0; i--) {
      document.getElementById(`step-${i}-edit-${id}`).classList.add('active');
    }

    if (step === 3) {
      modalNextStep2.classList.add(hiddenClass);
      modalNextStep3.classList.remove(hiddenClass);
    }

    if (step === 4) {
      modalNextStep2.classList.add(hiddenClass);
      modalNextStep3.classList.add(hiddenClass);
      modalUpdate.classList.remove(hiddenClass);
    }
  }

  !ids.includes(id) && modalPrev.addEventListener('click', function() {
    document.getElementById(`step-${step}-edit-${id}`).classList.remove('current');
    step--;
    document.getElementById(`step-${step}-edit-${id}`).classList.remove('active');

    if (step === 1 || (step === 2 && isEditAll === false)) {
      modalPrev.classList.add(hiddenClass);
    }

    if (step === 2) {
      modalNextStep2.classList.remove(hiddenClass);
      modalNextStep3.classList.add(hiddenClass);
    }

    if (step === 3) {
      modalNextStep3.classList.remove(hiddenClass);
      modalUpdate.classList.add(hiddenClass);
    }

    changeModalContentEdit();
  });

  /* Image Upload */
  !ids.includes(id) && uploadImageBtn.addEventListener('click', function(e) {
    e.preventDefault();
    uploader = wp.media({
      title: 'Đổi hình ảnh',
      button: {
        text: 'Sử dụng ảnh này'
      },
      multiple: false
    }).on('select', function() {
      var attachment = uploader.state().get('selection').first().toJSON();
      document.getElementById(`image-url-edit-${id}`).value = attachment.url;
      img = document.createElement('img');
      img.src = attachment.url;
      const wrapper = document.getElementById(`image-wrapper-edit-${id}`);
      wrapper.innerHTML = '';
      wrapper.appendChild(img);
    })
    .open();
  });

  handleInputChange(modal);

  ids.push(id);
}

function openEditAllModal() {
  triggerEditAll++;
  isEditAll = true;
  stepEditAll = 1;
  const modalOverlay = document.getElementById('overlay');
  const modalEditAll = document.getElementById(`modal-edit-all-ranking`);
  const modalNextStep1 = document.getElementById(`modal-next-edit-step-1-all`);
  const modalNextStep2 = document.getElementById(`modal-next-edit-step-2-all`);
  const modalNextStep3 = document.getElementById(`modal-next-edit-step-3-all`);
  const modalPrevEditAll = document.getElementById(`modal-prev-edit-all`);
  const modalUpdate = document.getElementById(`modal-update-edit-all`);
  modalOverlay.classList.remove(hiddenClass);
  modalEditAll.classList.remove(hiddenClass);
  modalPrevEditAll.classList.add(hiddenClass);
  modalUpdate.classList.add(hiddenClass);
  modalNextStep1.classList.remove(hiddenClass);
  modalNextStep2.classList.add(hiddenClass);
  modalNextStep3.classList.add(hiddenClass);

  for (let i = 1; i <= 4; i++) {
    document.getElementById(`step-${i}-edit-all`).classList.remove('current');
    document.getElementById(`step-${i}-edit-all`).classList.remove('active');
  }

  document.getElementById(`step-${stepEditAll}-edit-all`).classList.add('current');

  for (let i = stepEditAll - 1; i > 0; i--) {
    document.getElementById(`step-${i}-edit-all`).classList.add('active');
  }

  changeModalContentEditAll();

  triggerEditAll <= 1 && modalNextStep1.addEventListener('click', function() {
    let checkPassStep1 = 0;
    if (stepEditAll === 1) {
      const contentStep1 = document.querySelector('.content-step-1-edit-all');
      const requireInputStep1 = contentStep1.querySelectorAll('.require-field');

      requireInputStep1.forEach((input) => {
        if (input.value === '' || input.value < 0) {
          input.nextElementSibling.classList.remove(hiddenClass);
        } else {
          checkPassStep1++;
          input.nextElementSibling.classList.add(hiddenClass);
        }
      });

      if (checkPassStep1 === requireInputStep1.length) {
        nextStep();
      }
    }
  });

  triggerEditAll <= 1 && modalNextStep2.addEventListener('click', function() {
    let checkPassStep2 = 0;
    if (stepEditAll === 2) {
      const contentStep2 = document.querySelector('.content-step-2-edit-all');
      const requireInputStep2 = contentStep2.querySelectorAll('.require-field');

      requireInputStep2.forEach((input) => {
        if (input.value === '' || input.value < 0) {
          input.nextElementSibling.classList.remove(hiddenClass);
        } else {
          checkPassStep2++;
          input.nextElementSibling.classList.add(hiddenClass);
        }
      });

      if (checkPassStep2 === requireInputStep2.length) {
        nextStep();
      }
    }
  });

  triggerEditAll <= 1 && modalNextStep3.addEventListener('click', function() {
    let checkPassStep3 = 0;
    const tableStep3 = document.querySelector('#table-step-3-edit');
    const trStep3 = tableStep3.querySelectorAll('tr');
    trStep3.forEach((tr) => {
      let checkStep3 = 0;
      const requireInputStep3 = tr.querySelector('.require-field');
      const requireInputLimitStep3 = tr.querySelector('.require-field-limit');
      const limitInput = tr.querySelector('.is-limit-input');

      if (requireInputStep3.value === '' || requireInputStep3.value < 0) {
        requireInputStep3.nextElementSibling.classList.remove(hiddenClass);
      } else {
        requireInputStep3.nextElementSibling.classList.add(hiddenClass);
        checkStep3++;
      }

      if (limitInput.checked) {
        if (requireInputLimitStep3.value === '' || requireInputLimitStep3.value < 0) {
          requireInputLimitStep3.nextElementSibling.classList.remove(hiddenClass);
        } else {
          requireInputLimitStep3.nextElementSibling.classList.add(hiddenClass);
          checkStep3++;
        }

        if (checkStep3 === 2) {
          checkPassStep3++;
        }
      } else {
        if (checkStep3 === 1) {
          checkPassStep3++;
        }
      }
    });

    if (checkPassStep3 === trStep3.length) {
      nextStep();
    }
  });

  function nextStep() {
    stepEditAll++;
    changeModalContentEditAll();
    modalPrevEditAll.classList.remove(hiddenClass);
    document.getElementById(`step-${stepEditAll}-edit-all`).classList.add('current');

    for (let i = stepEditAll - 1; i > 0; i--) {
      document.getElementById(`step-${i}-edit-all`).classList.add('active');
    }

    if (stepEditAll === 2) {
      modalNextStep1.classList.add(hiddenClass);
      modalNextStep2.classList.remove(hiddenClass);
    }

    if (stepEditAll === 3) {
      modalNextStep2.classList.add(hiddenClass);
      modalNextStep3.classList.remove(hiddenClass);
    }

    if (stepEditAll === 4) {
      modalNextStep2.classList.add(hiddenClass);
      modalNextStep3.classList.add(hiddenClass);
      modalUpdate.classList.remove(hiddenClass);
    }
  }

  triggerEditAll <= 1 && modalPrevEditAll.addEventListener('click', function() {
    document.getElementById(`step-${stepEditAll}-edit-all`).classList.remove('current');
    stepEditAll--;
    document.getElementById(`step-${stepEditAll}-edit-all`).classList.remove('active');

    if (stepEditAll === 1 || (stepEditAll === 2 && isEditAll === false)) {
      modalPrevEditAll.classList.add(hiddenClass);
      modalNextStep1.classList.remove(hiddenClass);
      modalNextStep2.classList.add(hiddenClass);
    }

    if (stepEditAll === 2) {
      modalNextStep2.classList.remove(hiddenClass);
      modalNextStep3.classList.add(hiddenClass);
    }

    if (stepEditAll === 3) {
      modalNextStep3.classList.remove(hiddenClass);
      modalUpdate.classList.add(hiddenClass);
    }

    changeModalContentEditAll();
  });

  /* Image Upload */
  const step2Wrapper = document.querySelector('.content-step-2-edit-all');
  const allButtonImageUpload = step2Wrapper.querySelectorAll('.upload-image-button-edit-all');
  allButtonImageUpload.forEach((buttonUpload) => {
    buttonUpload.addEventListener('click', function(e) {
      e.preventDefault();
      uploader = wp.media({
        title: 'Đổi hình ảnh',
        button: {
          text: 'Sử dụng ảnh này'
        },
        multiple: false
      }).on('select', function() {
        const attachment = uploader.state().get('selection').first().toJSON();
        buttonUpload.nextElementSibling.value = attachment.url;
        img = document.createElement('img');
        img.src = attachment.url;
        const wrapper = buttonUpload.nextElementSibling.nextElementSibling;
        wrapper.innerHTML = '';
        wrapper.appendChild(img);
      })
      .open();
    });
  });

  handleInputChange(modalEditAll);

  const deleteEditRecord = document.querySelectorAll('.delete-edit-record');
  const numberOfRanking = document.getElementsByName('number_of_ranking')[0].value;

  if (numberOfRanking > 1) {
    deleteEditRecord.forEach((item) => {
      item.removeAttribute('disabled');
    });
  }

  deleteEditRecord.forEach((item) => {
    item.addEventListener('click', function() {
      const currentId = item.parentElement.parentElement.id.split('-')[5];
      item.parentElement.parentElement.remove();
      document.querySelector(`#record-step-3-edit-all-${currentId}`).remove();
      document.querySelector(`#record-step-4-edit-all-${currentId}`).remove();

      const deleteEditRecordCheck = document.querySelectorAll('.delete-edit-record');
      if (deleteEditRecordCheck.length === 1) {
        deleteEditRecordCheck[0].setAttribute('disabled', '');
      }
    });
  });
}

window.addEventListener('load', function() {
  /* Common */
  const removeMessageBtn = document.getElementById('remove-message');
  removeMessageBtn && removeMessageBtn.addEventListener('click', function() {
    document.getElementById('message').classList.add(hiddenClass);
  });

  /* Tabs */
  const sPageURL = window.location.search.substring(1);
  const params = sPageURL.split('&');
  
  const tabRank = document.getElementById('tabRank');
  const tabUser = document.getElementById('tabUser');
  const tab1 = document.getElementById('tab-1');
  const tab2 = document.getElementById('tab-2');
  const buttonAdd = document.getElementById('button-open-modal-add');
  const buttonEditAll = document.getElementById('button-open-modal-edit-all');

  if (tabRank) {
    if (params.length === 1 || params[2].split('=')[1] === 'dsxh') {
      tabRank.classList.add(activeClass);
      tabUser.classList.remove(activeClass);
      tab1.classList.add(activeClass);
      tab2.classList.remove(activeClass);
    } else if (params[2].split('=')[1] === 'dstv') {
      tabRank.classList.remove(activeClass);
      tabUser.classList.add(activeClass);
      tab1.classList.remove(activeClass);
      tab2.classList.add(activeClass);
      buttonAdd.classList.add(hiddenClass);
      buttonEditAll.classList.add(hiddenClass);
    }
  }

  const tabs = document.querySelectorAll('ul.nav-tabs > li');
  for (i = 0; i < tabs.length; i++) {
    tabs[i].addEventListener('click', switchTab);
  }

  function switchTab(event) {
    event.preventDefault();
    document.querySelector('ul.nav-tabs li.active').classList.remove(activeClass);
    document.querySelector('.tab-pane.active').classList.remove(activeClass);
    const clickedTab = event.currentTarget;
    const anchor = event.target;
    const activePaneID = anchor.getAttribute('href');
    clickedTab.classList.add(activeClass);
    document.querySelector(activePaneID).classList.add(activeClass);
  }

  /* Modal */
  let step = 1;
  let isEditAll = false;
  let listIdsDelete = [];
  let recordLength = 1;
  const modalOverlay = document.getElementById('overlay');
  const modalAddBtn = document.getElementById('button-open-modal-add');
  const modalDeleteBtn = document.getElementById('modal-delete-btn');
  const modalAdd = document.getElementById('modal-add');
  const modalClose = document.getElementById('modal-close');
  const modalNextStep2 = document.getElementById('modal-next-step-2');
  const modalNextStep3 = document.getElementById('modal-next-step-3');
  const modalPrev = document.getElementById('modal-prev');
  const modalUpdate = document.getElementById('modal-update');
  const listCheckbox = document.getElementsByName('checkRank');
  const buttonDeleteWrapper = document.getElementById('button-delete-wrapper');
  const checkboxAll = document.getElementById('checkAllRank');
  const modalDeleteAll = document.getElementById('modal-delete-all-ranking');
  const deleteList = document.getElementById('deleteList');
  const addMoreRecord = document.getElementById('add-more-record');
  const tableStep2 = document.getElementById('table-step-2');
  const tableStep3 = document.getElementById('table-step-3');
  const tableStep4 = document.getElementById('table-step-4');

  addMoreRecord && addMoreRecord.addEventListener('click', function() {
    recordLength++;
    const rowStep2 = document.createElement('tr');
    rowStep2.innerHTML = `
      <td class="flex-center">
        <button type="button" class="upload-image-button button flex-center">
          <span class="dashicons dashicons-admin-media"></span>
          <span>Tải lên</span>
        </button>
        <input type="text" class="image-url" hidden="" name="imageurl[]">
        <div class="image-wrapper" style="width: 50px; height: auto;"></div>
      </td>
      <td>
        <p class="required">Rank</p>
        <input class="rank-name require-field" type="text" name="name[]" placeholder="Vui lòng nhập Rank">
        <p class="form-error-text d-none">Đây là trường bắt buộc</p>
      </td>
      <td>
        <p class="required">Chi tiêu tối thiểu</p>
        <input class="require-field" type="number" name="minimum_spending[]" placeholder="Vui lòng nhập Chi tiêu tối thiểu">
        <p class="form-error-text d-none">Đây là trường bắt buộc</p>
      </td>
      <td>
        <span class="button delete delete-add-record">✕</span>
      </td>
    `;
    rowStep2.id = `record-step-2-add-${recordLength}`;
    tableStep2.appendChild(rowStep2);
    reTriggerUploadImage(rowStep2, recordLength);

    const rowStep3 = document.createElement('tr');
    rowStep3.innerHTML = `
      <td>
        <span class="dashicons dashicons-plus-alt"></span>
        <span class="show-rank"></span>
      </td>
      <td>
        <p class="required">Khuyến mãi</p>
        <input class="require-field" type="number" name="price_sale_off[]" placeholder="Vui lòng nhập Khuyến mãi">
        <p class="form-error-text d-none">Đây là trường bắt buộc</p>
      </td>
      <td>
        <input type="checkbox" class="is-limit-input" name="is_limit[]">
      </td>
      <td>
        <p>Không giới hạn số tiền</p>
        <div class="is-limit-content d-none">
          <p class="required">Số tiền khuyến mãi tối đa cho một đơn hàng</p>
          <input class="price-sale-off-max require-field require-field-limit" type="number" name="price_sale_off_max[]" placeholder="Vui lòng nhập Số tiền khuyến mãi tối đa cho một đơn hàng">
          <p class="form-error-text d-none">Đây là trường bắt buộc</p>
        </div>
      </td>
    `;
    rowStep3.id = `record-step-3-add-${recordLength}`;
    tableStep3.appendChild(rowStep3);

    const rowStep4 = document.createElement('tr');
    rowStep4.innerHTML = `
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
    `;
    rowStep4.id = `record-step-4-add-${recordLength}`;
    tableStep4.appendChild(rowStep4);

    handleInputChange(modalAdd);

    const deleteAddRecord = document.querySelectorAll('.delete-add-record');

    if (recordLength > 1) {
      deleteAddRecord.forEach((item) => {
        item.removeAttribute('disabled');
      });
    }

    deleteAddRecord.forEach((item) => {
      item.addEventListener('click', function() {
        const currentId = item.parentElement.parentElement.id.split('-')[4];
        item.parentElement.parentElement.remove();
        const step3Delete = document.querySelector(`#record-step-3-add-${currentId}`);
        const step4Delete = document.querySelector(`#record-step-4-add-${currentId}`);
        step3Delete && step3Delete.remove();
        step4Delete && step4Delete.remove();

        const deleteAddRecordCheck = document.querySelectorAll('.delete-add-record');
        if (deleteAddRecordCheck.length === 1) {
          deleteAddRecordCheck[0].setAttribute('disabled', '');
        }
      });
    });
  });

  function reTriggerUploadImage(parent, number) {
    const uploadImageBtn = parent.querySelector('.upload-image-button');
    uploadImageBtn.addEventListener('click', function(e) {
      e.preventDefault();
      uploader = wp.media({
        title: 'Thêm hình ảnh',
        button: {
          text: 'Sử dụng ảnh này'
        },
        multiple: false
      }).on('select', function() {
        const attachment = uploader.state().get('selection').first().toJSON();
        const tdRecord = document.getElementById(`record-step-2-add-${number}`);
        tdRecord.querySelector('.image-url').value = attachment.url;
        img = document.createElement('img');
        img.src = attachment.url;
        const wrapper = tdRecord.querySelector('.image-wrapper');
        wrapper.innerHTML = '';
        wrapper.appendChild(img);
      })
      .open();
    });
  }

  checkboxAll && checkboxAll.addEventListener('click', function(e) {
    if (e.target.checked) {
      if (listCheckbox.length) {
        buttonDeleteWrapper.classList.remove('d-none');
        listCheckbox.forEach((item) => {
          listIdsDelete.push({
            id: item.value.split('-')[0],
            rankName: item.value.split('-')[1]
          });
        });
      }
    } else {
      buttonDeleteWrapper.classList.add('d-none');
      listIdsDelete = [];
    }
    listIdsDelete = [...new Set(listIdsDelete.map(JSON.stringify))].map(JSON.parse);
  });

  listCheckbox.forEach((item) => {
    item.addEventListener('click', function() {
      if (item.checked) {
        buttonDeleteWrapper.classList.remove('d-none');
        listIdsDelete.push({
          id: item.value.split('-')[0],
          rankName: item.value.split('-')[1]
        });
      } else {
        listIdsDelete = listIdsDelete.filter(({ id }) => Number(id) !== Number(item.value.split('-')[0]));
        check = 0;
        listCheckbox.forEach((item2) => {
          if (item2.checked) {
            check++;
          }
        });
        if (check === 0) {
          buttonDeleteWrapper.classList.add('d-none');
        }
      }
    });
  });

  modalAddBtn && modalAddBtn.addEventListener('click', function() {
    openModal();
  });

  modalDeleteBtn && modalDeleteBtn.addEventListener('click', function() {
    deleteList.innerHTML = '';
    modalOverlay.classList.remove(hiddenClass);
    modalDeleteAll.classList.remove(hiddenClass);

    listIdsDelete.forEach(({ id }) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'id-delete[]';
      input.value = id;
      deleteList.appendChild(input);
    });

    listIdsDelete.forEach(({ rankName }, index) => {
      const span = document.createElement('span');
      span.innerHTML = index === 0 ? rankName : `, ${rankName}`;
      deleteList.appendChild(span);
    });
  });

  modalClose && modalClose.addEventListener('click', function() {
    modalOverlay.classList.add(hiddenClass);
    modalAdd.classList.add(hiddenClass);
  });

  modalNextStep2 && modalNextStep2.addEventListener('click', function() {
    let checkPassStep2 = 0;
    if (step === 2) {
      const contentStep2 = document.querySelector('.content-step-2');
      const requireInputStep2 = contentStep2.querySelectorAll('.require-field');

      requireInputStep2.forEach((input) => {
        if (input.value === '' || input.value < 0) {
          input.nextElementSibling.classList.remove(hiddenClass);
        } else {
          checkPassStep2++;
          input.nextElementSibling.classList.add(hiddenClass);
        }
      });

      if (checkPassStep2 === requireInputStep2.length) {
        nextStep();
      }
    }
  });

  modalNextStep3 && modalNextStep3.addEventListener('click', function() {
    let passStep3 = 0;
    if (step === 3) {
      const tableStep3 = document.querySelector('#table-step-3');
      const trStep3 = tableStep3.querySelectorAll('tr');
      trStep3.forEach((tr) => {
        let checkStep3 = 0;
        const requireInputStep3 = tr.querySelector('.require-field');
        const requireInputLimitStep3 = tr.querySelector('.require-field-limit');
        const limitInput = tr.querySelector('.is-limit-input');

        if (requireInputStep3.value === '' || requireInputStep3.value < 0) {
          requireInputStep3.nextElementSibling.classList.remove(hiddenClass);
        } else {
          requireInputStep3.nextElementSibling.classList.add(hiddenClass);
          checkStep3++;
        }

        if (limitInput.checked) {
          if (requireInputLimitStep3.value === '' || requireInputLimitStep3.value < 0) {
            requireInputLimitStep3.nextElementSibling.classList.remove(hiddenClass);
          } else {
            requireInputLimitStep3.nextElementSibling.classList.add(hiddenClass);
            checkStep3++;
          }

          if (checkStep3 === 2) {
            passStep3++;
          }
        } else {
          if (checkStep3 === 1) {
            passStep3++;
          }
        }
      });

      if (passStep3 === trStep3.length) {
        nextStep();
      }
    }
  });

  function nextStep() {
    step++;
    changeModalContent();
    modalPrev.classList.remove(hiddenClass);
    document.getElementById(`step-${step}`).classList.add('current');

    for (let i = step - 1; i > 0; i--) {
      document.getElementById(`step-${i}`).classList.add(activeClass);
    }

    if (step === 3) {
      modalNextStep2.classList.add(hiddenClass);
      modalNextStep3.classList.remove(hiddenClass);
    }

    if (step === 4) {
      modalNextStep2.classList.add(hiddenClass);
      modalNextStep3.classList.add(hiddenClass);
      modalUpdate.classList.remove(hiddenClass);
    }
  }

  modalPrev && modalPrev.addEventListener('click', function() {
    document.getElementById(`step-${step}`).classList.remove('current');
    step--;
    document.getElementById(`step-${step}`).classList.remove(activeClass);

    if (step === 1 || (step === 2 && isEditAll === false)) {
      modalPrev.classList.add(hiddenClass);
    }

    if (step === 2) {
      modalNextStep2.classList.remove(hiddenClass);
      modalNextStep3.classList.add(hiddenClass);
    }

    if (step === 3) {
      modalNextStep3.classList.remove(hiddenClass);
      modalUpdate.classList.add(hiddenClass);
    }

    changeModalContent();
  });

  function openModal() {
    step = isEditAll ? 1 : 2;
    modalPrev.classList.add(hiddenClass);
    modalUpdate.classList.add(hiddenClass);
    modalOverlay.classList.remove(hiddenClass);
    modalAdd.classList.remove(hiddenClass);
    modalNextStep2.classList.remove(hiddenClass);
    modalNextStep3.classList.add(hiddenClass);

    for (let i = 1; i <= 4; i++) {
      document.getElementById(`step-${i}`).classList.remove('current');
      document.getElementById(`step-${i}`).classList.remove(activeClass);
    }

    document.getElementById(`step-${step}`).classList.add('current');

    for (let i = step - 1; i > 0; i--) {
      document.getElementById(`step-${i}`).classList.add(activeClass);
    }

    changeModalContent();
  }

  function changeModalContent() {
    for (let i = 1; i <= 4; i++) {
      if (i === step) {
        document.getElementsByClassName(`content-step-${i}`)[0].classList.remove(hiddenClass);
      } else {
        document.getElementsByClassName(`content-step-${i}`)[0].classList.add(hiddenClass);
      }
    }
  }

  /* Image Upload */
  const uploadImageBtn = document.querySelector('.upload-image-button');
  uploadImageBtn && uploadImageBtn.addEventListener('click', function(e) {
    e.preventDefault();
    uploader = wp.media({
      title: 'Thêm hình ảnh',
      button: {
        text: 'Sử dụng ảnh này'
      },
      multiple: false
    }).on('select', function() {
      const attachment = uploader.state().get('selection').first().toJSON();
      const tdRecord = document.getElementById(`record-step-2-add-1`);
      tdRecord.querySelector('.image-url').value = attachment.url;
      img = document.createElement('img');
      img.src = attachment.url;
      const wrapper = tdRecord.querySelector('.image-wrapper');
      wrapper.innerHTML = '';
      wrapper.appendChild(img);
    })
    .open();
  });

  handleInputChange(modalAdd);
});
