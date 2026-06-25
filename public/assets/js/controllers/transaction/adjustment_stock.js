let objInput = null;
let elmChoose;
let AdjustmentStock = {
  module: () => {
    return "transaksi/adjustment_stock";
  },

  csrf_token: () => {
    return $('meta[name="csrf-token"]').attr("content");
  },

  moduleApi: () => {
    return "api/" + AdjustmentStock.module();
  },

  moduleApiUsers: () => {
    return "api/master/users";
  },

  setSelect2: () => {
    if ($(".select2").length > 0) {
      $.each($(".select2"), function () {
        $(this).select2();
      });
    }
  },

  cancel: (elm, e) => {
    e.preventDefault();
    window.location.href = url.base_url(AdjustmentStock.module()) + "/";
  },

  add: (elm, e) => {
    e.preventDefault();
    window.location.href = url.base_url(AdjustmentStock.module()) + "add";
  },

  addItem: (elm, e) => {
    e.preventDefault();
    let table = $(elm)
      .closest("div")
      .find("table#table-routing")
      .find("tbody")
      .find("tr.input:last");
    let newTr = table.clone();
    newTr.find("input").val("");
    newTr.attr("data_id", "");
    newTr
      .find("td#action")
      .html(
        `<button type="button" onclick="AdjustmentStock.deleteItem(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`,
      );
    table.after(newTr);
  },

  addReminderItem: (elm, e) => {
    e.preventDefault();
    let table = $(elm)
      .closest("div")
      .find("table#table-routing-reminder")
      .find("tbody")
      .find("tr.input:last");
    let newTr = table.clone();
    newTr.find("input").val("");
    newTr.attr("data_id", "");
    newTr
      .find("td#action")
      .html(
        `<button type="button" onclick="AdjustmentStock.deleteItem(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`,
      );
    table.after(newTr);
  },

  deleteItem: (elm) => {
    let data_id = $(elm).closest("tr").attr("data_id");
    if (data_id == "") {
      $(elm).closest("tr").remove();
    } else {
      $(elm).closest("tr").addClass("remove");
      $(elm).closest("tr").addClass("hide");
    }
  },

  getPostItem: () => {
    let data = $("table#table-routing").find("tbody").find("tr.input");
    let result = [];
    data.each((index, elm) => {
      result.push({
        id: $(elm).attr("data_id"),
        product: $(elm).find("input#product").val(),
        qty: $(elm).find("#qty").val(),
        price: $(elm).find("td#unit_price").text(),
        unit: $(elm).find("td#unit").attr('data_id'),
        remove: $(elm).hasClass("remove") ? 1 : 0,
      });
    });

    return result;
  },

  getPostReminderItem: () => {
    let data = $("table#table-routing-reminder").find("tbody").find("tr.input");
    let result = [];
    data.each((index, elm) => {
      result.push({
        id: $(elm).attr("data_id"),
        users: $(elm).find("input#users").val(),
        remove: $(elm).hasClass("remove") ? 1 : 0,
      });
    });

    return result;
  },

  getPostInput: () => {
    let data = {
      id: $("input#id").val(),
      remarks: $("#remarks").val(),
      warehouse_id: $("#warehouse_id").val(),
      routing: AdjustmentStock.getPostItem(),
    };

    return data;
  },

  submit: (elm, e) => {
    e.preventDefault();
    let form = $(elm).closest("div.row");
    if (validation.runWithElement(form)) {
      let params = AdjustmentStock.getPostInput();
      $.ajax({
        type: "POST",
        dataType: "json",
        data: params,
        url: url.base_url(AdjustmentStock.moduleApi()) + "submit",
        headers: {
          "X-CSRF-TOKEN": AdjustmentStock.csrf_token(),
        },
        beforeSend: () => {
          message.loadingProses("Proses Simpan Data...");
        },
        error: function () {
          message.closeLoading();
          message.sweetError("Informasi", "Gagal");
        },

        success: function (resp) {
          message.closeLoading();
          if (resp.is_valid) {
            message.sweetSuccess();
            setTimeout(function () {
              // window.location.reload();
              AdjustmentStock.back();
            }, 1000);
          } else {
            message.sweetError("Informasi", resp.message);
          }
        },
      });
    } else {
      message.sweetError("Informasi", "Data Belum Lengkap");
    }
  },

  back: (elm) => {
    window.location.href = url.base_url(AdjustmentStock.module()) + "/";
  },

  getData: async () => {
    let tableData = $("table#table-data");

    let updateAction = $("#update").val();
    let deleteAction = $("#delete").val();

    var data = tableData.DataTable({
      processing: true,
      serverSide: true,
      ordering: true,
      autoWidth: false,
      order: [[0, "asc"]],
      aLengthMenu: [
        [25, 50, 100],
        [25, 50, 100],
      ],
      lengthChange: !1,
      language: {
        paginate: {
          previous: "<i class='mdi mdi-chevron-left'>",
          next: "<i class='mdi mdi-chevron-right'>",
        },
      },
      drawCallback: function () {
        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
      },
      ajax: {
        url: url.base_url(AdjustmentStock.moduleApi()) + `getData`,
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": AdjustmentStock.csrf_token(),
        },
      },
      deferRender: true,
      createdRow: function (row, data, dataIndex) {
        // console.log('row', $(row));
      },
      buttons: ["copy", "excel", "pdf", "colvis"],
      columns: [
        {
          data: "id",
          render: function (data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          },
        },
        {
          data: "code",
        },
        {
          data: "wh_name",
        },
        {
          data: "remarks",
        },
        {
          data: "id",
          render: function (data, type, row) {
            var html = "";
            if (updateAction == 1) {
              html += `<a href='${url.base_url(
                AdjustmentStock.module(),
              )}ubah?id=${data}' data_id="${row.id
                }" class="btn btn-success editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
            }
            if (deleteAction == 1) {
              html += `<button type="button" data_id="${row.id}" onclick="AdjustmentStock.delete(this, event)" class="btn btn-danger editable-cancel btn-sm waves-effect waves-light"><i class="bx bx-trash-alt"></i></button>`;
            }
            return html;
          },
        },
      ],
    });

    (data
      .buttons()
      .container()
      .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
      $(".dataTables_length select").addClass("form-select form-select-sm"),
      $("#selection-datatable").DataTable({
        select: {
          style: "multi",
        },
        language: {
          paginate: {
            previous: "<i class='mdi mdi-chevron-left'>",
            next: "<i class='mdi mdi-chevron-right'>",
          },
        },
        drawCallback: function () {
          $(".dataTables_paginate > .pagination").addClass(
            "pagination-rounded",
          );
        },
      }));
  },

  delete: (elm, e) => {
    e.preventDefault();
    let params = {};
    params.id = $(elm).attr("data_id");
    $.ajax({
      type: "POST",
      dataType: "html",
      data: params,
      url: url.base_url(AdjustmentStock.moduleApi()) + "delete",
      headers: {
        "X-CSRF-TOKEN": AdjustmentStock.csrf_token(),
      },
      beforeSend: () => {
        message.loadingProses("Proses Pengambilan Data...");
      },
      error: function () {
        message.closeLoading();
        message.sweetError("Informasi", "Gagal");
      },

      success: function (resp) {
        message.closeLoading();
        $("#content-confirm-delete").html(resp);
        $("#confirm-delete-btn").trigger("click");
      },
    });
  },

  confirmDelete: (elm) => {
    let params = {};
    params.id = $(elm).attr("data_id");
    $.ajax({
      type: "POST",
      dataType: "json",
      data: params,
      url: url.base_url(AdjustmentStock.moduleApi()) + "confirmDelete",
      headers: {
        "X-CSRF-TOKEN": AdjustmentStock.csrf_token(),
      },
      beforeSend: () => {
        message.loadingProses("Proses Simpan Data...");
      },
      error: function () {
        message.closeLoading();
        message.sweetError("Informasi", "Gagal");
      },

      success: function (resp) {
        message.closeLoading();
        if (resp.is_valid) {
          message.sweetSuccess("Informasi", "Data Berhasil Dihapus");
          setTimeout(function () {
            window.location.reload();
          }, 1000);
        } else {
          message.sweetError("Informasi", resp.message);
        }
      },
    });
  },

  setDate: () => {
    $("#search-datepicker").flatpickr({});
  },

  showDataProduct: (elm) => {
    let params = {};

    $.ajax({
      type: "POST",
      dataType: "html",
      data: params,
      url: url.base_url(AdjustmentStock.moduleApi()) + "showDataProduct",
      headers: {
        "X-CSRF-TOKEN": AdjustmentStock.csrf_token(),
      },

      beforeSend: () => {
        message.loadingProses("Proses Pengambilan Data");
      },

      error: function () {
        message.closeLoading();
        message.sweetError("Informasi", "Gagal");
      },

      success: function (resp) {
        message.closeLoading();
        $("#content-modal-form").html(resp);
        $("#btn-show-modal").trigger("click");
        objInput = elm;
        elmChoose = elm;
        AdjustmentStock.getDataProduct();
      },
    });
  },

  getDataProduct: () => {
    let tableData = $("table#table-data-modal");
    const params = {
      customer: $("#customer_id").val(),
      principal: $("#principal").val(),
    };

    var data = tableData.DataTable({
      processing: true,
      serverSide: true,
      ordering: true,
      autoWidth: false,
      order: [[0, "asc"]],
      aLengthMenu: [
        [25, 50, 100],
        [25, 50, 100],
      ],
      // lengthChange: !1,
      language: {
        paginate: {
          previous: "<i class='mdi mdi-chevron-left'>",
          next: "<i class='mdi mdi-chevron-right'>",
        },
      },
      drawCallback: function () {
        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
      },
      ajax: {
        url: url.base_url(AdjustmentStock.moduleApi()) + `getDataProduct`,
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": AdjustmentStock.csrf_token(),
        },
        data: function (d) {
          d.principal = $("#principal").val(); // ambil nilai status dari elemen input/select
          d.customer = $("#customer_id").val();
        },
      },
      deferRender: true,
      createdRow: function (row, data, dataIndex) {
        // console.log('row', $(row));
      },
      buttons: ["copy", "excel", "pdf", "colvis"],
      columns: [
        {
          data: "id",
          render: function (data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          },
        },
        {
          data: "nama_vendor",
        },
        {
          data: "code",
        },
        {
          data: "name",
        },
        {
          data: "unit_tujuan_name",
        },
        {
          data: "min_qty",
        },
        {
          data: "max_qty",
        },
        {
          data: "customer_name",
        },
        {
          data: "harga",
        },
        {
          data: "date_start",
        },
        {
          data: "stock_product",
        },
        {
          data: "id",
          render: function (data, type, row) {
            var html = "";
            html += `<a href='' produk_id="${row.id}" unit="${row.unit_tujuan_id}" unit_name="${row.unit_tujuan_name}"
                            code="${row.code}" produk_name="${row.name}"
                            price="${row.harga}"
                            price_id="${row.price_id}"
                            onclick="AdjustmentStock.pilihDataProduct(this, event)"
                            data_id="${row.id_uom}" class="btn btn-info editable-submit btn-sm waves-effect waves-light"><i class="bx bx-edit"></i></a>&nbsp;`;
            return html;
          },
        },
      ],
    });
  },

  filterPrincipal: (elm) => {
    const principal = $("#principal").val();
    $("#principal").val(principal);
    $("#table-data-modal").DataTable().ajax.reload();
  },

  pilihData: (elm, e) => {
    e.preventDefault();
    let nama_lengkap = $(elm).attr("nama_lengkap");
    let dataId = $(elm).attr("data_id");
    if (objInput != null) {
      $(objInput)
        .closest("tr")
        .find("input#users")
        .val(dataId + "//" + nama_lengkap);
    }
    $("button.btn-close").trigger("click");
  },

  pilihDataProduct: (elm, e) => {
    e.preventDefault();
    let produk_name = $(elm).attr("produk_name");
    let produk_id = $(elm).attr("produk_id");
    let unit = $(elm).attr("unit");
    let unit_name = $(elm).attr("unit_name");
    let product_uom_id = $(elm).attr("data_id");
    let price = $(elm).attr("price");
    let price_id = $(elm).attr("price_id");
    $(elmChoose)
      .closest("div")
      .find("input")
      .val(product_uom_id + "//" + produk_id + "//" + produk_name);
    $(elmChoose).closest("div").find("input").attr("data_id", produk_id);

    $(elmChoose).closest("tr").find("td#unit").text(unit_name);
    $(elmChoose).closest("tr").find("td#unit").attr("data_id", unit);
    $(elmChoose).closest("tr").find("td#unit_price").text(price);
    $(elmChoose)
      .closest("tr")
      .find("td#unit_price")
      .attr("data_id", price_id);
    $("button.btn-close").trigger("click");
  },
};

$(function () {
  AdjustmentStock.setDate();
  AdjustmentStock.setSelect2();
  AdjustmentStock.getData();
});
