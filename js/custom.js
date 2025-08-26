$(document).ready(function () {




  // $("input:text:visible:first").focus();

  // Map [Enter] key to work like the [Tab] key
  // Daniel P. Clark 2014

  // Catch the keydown for the entire document
  $(document).keydown(function (e) {

    // Set self as the current item in focus
    var self = $(':focus'),
      // Set the form by the current item in focus
      form = self.parents('form:eq(0)'),
      focusable;

    // Array of Indexable/Tab-able items
    focusable = form.find('input,a,select,button,textarea,div[contenteditable=true]').filter(':visible');

    function enterKey() {
      if (e.which === 13 && !self.is('textarea,div[contenteditable=true]')) { // [Enter] key

        // If not a regular hyperlink/button/textarea
        if ($.inArray(self, focusable) && (!self.is('a,button'))) {
          // Then prevent the default [Enter] key behaviour from submitting the form
          e.preventDefault();
        } // Otherwise follow the link/button as by design, or put new line in textarea

        // Focus on the next item (either previous or next depending on shift)
        focusable.eq(focusable.index(self) + (e.shiftKey ? -1 : 1)).focus();
        focusable.eq(focusable.index(self) + (e.shiftKey ? -1 : 1)).select();


        return false;
      }
    }
    // We need to capture the [Shift] key and check the [Enter] key either way.
    if (e.shiftKey) {
      enterKey()
    } else {
      enterKey()
    }
  });



  document.onkeyup = function (e) {
    // if (e.which == 13) {
    //   //enter press
    //    e.preventDefault();
    // $("#get_product_price").focus();

    //  // subAmount();
    // } 
    if (e.altKey && e.which == 83) {
      //s press
      $("#sale_order_btn").click();

    }


    if (e.altKey && e.which == 80) {
      //alt+p press

      $("#paid_ammount").focus();

    }
    if (e.altKey && e.which == 79) {
      console.log('asd');

      //$(".searchableSelect").open();
      $('.searchableSelect select').val(2).trigger('change');



    }



  };
  $("#formData").on('submit', function (e) {
    e.preventDefault();
    var form = $('#formData');
    $.ajax({
      type: 'POST',
      url: form.attr('action'),
      data: form.serialize(),
      dataType: 'json', // ✅ Add this line
      beforeSend: function () {
        $('#formData_btn').prop("disabled", true);
      },
      success: function (response) {

        if (response.sts == "success") {
          $('#formData')[0].reset();
          $("#tableData").load(location.href + " #tableData > *");
          $('.modal').modal('hide');
        }

        Swal.fire({
          title: response.sts === "success" ? "Success" : "Error",
          text: response.msg || "No message provided",
          icon: response.sts,
          timer: 1500,
          showConfirmButton: false
        });

        $('#formData_btn').prop("disabled", false);
      },
      error: function (xhr, status, error) {
        $('#formData_btn').prop("disabled", false);
        console.log("AJAX Error:", error);
        Swal.fire({
          title: "Error",
          text: "Something went wrong. Please try again.",
          icon: "error",
          timer: 1500,
          showConfirmButton: false
        });
      }
    });
  });

  $("#formData1").on('submit', function (e) {
    e.stopPropagation();
    e.preventDefault();
    var form = $('#formData1');
    $.ajax({
      type: 'POST',
      url: form.attr('action'),
      data: form.serialize(),
      dataType: 'json',
      beforeSend: function () {
        $('#formData1_btn').prop("disabled", true);
      },
      success: function (response) {

        if (response.sts == "success") {
          $('#formData1').each(function () {
            this.reset();
          });
          //$("#tableData").load(location.href+" #tableData"); 
          $('.modal').modal('hide');
        }
        $('#formData1_btn').prop("disabled", false);

        $("#tableData1").load(location.href + " #tableData1 > *");

        sweeetalert(response.msg, response.sts, 1500);
      }
    }); //ajax call
  }); //main
  $("#formData2").on('submit', function (e) {
    e.stopPropagation();
    e.preventDefault();
    var form = $('#formData2');
    $.ajax({
      type: 'POST',
      url: form.attr('action'),
      data: form.serialize(),
      dataType: 'json',
      beforeSend: function () {
        $('#formData2_btn').prop("disabled", true);
      },
      success: function (response) {

        if (response.sts == "success") {
          $('#formData2').each(function () {
            this.reset();
          });
          //$("#tableData").load(location.href+" #tableData"); 
          $('.modal').modal('hide');
        }
        $('#formData2_btn').prop("disabled", false);

        $("#tableData2").load(location.href + " #tableData2 > *");

        sweeetalert(response.msg, response.sts, 1500);
      }
    }); //ajax call
  }); //main
  $("#sale_order_fm").on('submit', function (e) {
    e.preventDefault();

    var form = $('#sale_order_fm');
    console.log(form.serialize());


    $.ajax({
      type: 'POST',
      url: form.attr('action'),
      data: form.serialize(),
      dataType: 'json',
      beforeSend: function () {
        $('#sale_order_print').prop("disabled", true);
        $('#sale_order_btn').prop("disabled", true);
      },
      success: function (response) {
        if (response.sts === "success") {
          // Reset the form
          form[0].reset();
          $('#ratetype').trigger('change');;

          // Clear table and totals
          $('#purchase_product_tb').html('');
          $('#product_grand_total_amount').html('');
          $('#product_total_amount').html('');

          // alert(response.type);
          // Print the order
          if (response.type == 'order') {
            printOrder(response.order_id);
          } else if (response.type == 'purchase') {
            sweeetalert(response.msg, response.sts, 1000);
          }
        }

        if (response.sts === "error") {
          sweeetalert(response.msg, response.sts, 1500);
        }

        $('#sale_order_btn').prop("disabled", false);
        $('#sale_order_print').prop("disabled", false);
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        sweeetalert("An unexpected error occurred.", "error", 2000);
        $('#sale_order_btn').prop("disabled", false);
        $('#sale_order_print').prop("disabled", false);
      }
    });
  });

  $("#credit_order_client_name").on('change', function () {
    var value = $('#credit_order_client_name :selected').data('id');
    var contact = $('#credit_order_client_name :selected').data('contact');
    $('#customer_account').val(value);
    $('#client_contact').val(contact);
  });

  $("#add_product_fm").on('submit', function (e) {
    e.preventDefault();

    var form = $(this);
    var fd = new FormData(this);

    $.ajax({
      url: form.attr('action'),
      type: form.attr('method'),
      data: fd,
      dataType: "json",
      contentType: false,
      processData: false,
      beforeSend: function () {
        $('#add_product_btn').prop("disabled", true);
      },
      success: function (response) {
        console.log('click');

        if (response.sts === "success") {
          // Reset form
          $('#add_product_fm')[0].reset();

          // Focus the product code input
          $('#product_code').focus();
        }

        $('#add_product_btn').prop("disabled", false);

        var product_add_from = $('#product_add_from').val();
        if (product_add_from === "modal") {
          $("#get_product_name").load(location.href + " #get_product_name > *");
          $('#add_product_modal').modal('hide');
        }

        // Fix typo if you mean SweetAlert2
        sweeetalert(response.msg, response.sts, 1500);
        // OR use SweetAlert2 (if you're using it):
        // Swal.fire({ icon: response.sts, title: response.msg, timer: 1500, showConfirmButton: false });
      }
    });
  });


  $("#voucher_general_fm").on('submit', function (e) {
    e.preventDefault();
    var form = $('#voucher_general_fm');
    $.ajax({
      type: 'POST',
      url: form.attr('action'),
      data: form.serialize(),
      dataType: 'json',
      beforeSend: function () {
        $('#voucher_general_btn').prop("disabled", true);
      },
      success: function (response) {
        if (response.sts == "success") {
          $('#voucher_general_fm').each(function () {
            this.reset();
          });
          $("#tableData").load(location.href + " #tableData");
        }
        $('#voucher_general_btn').prop("disabled", false);

        Swal.fire({
          title: response.msg,
          showDenyButton: true,
          showCancelButton: true,
          confirmButtonText: `Print`,
          denyButtonText: `Add New`,
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Which type of do you Print?',
              showDenyButton: true,
              showCancelButton: true,
              confirmButtonText: `Debit`,
              denyButtonText: `Credit`,
              cancelButtonText: 'Both',
            }).then((result) => {
              if (result.isConfirmed) {
                window.open('print_voucher.php?type=debit&voucher_id=' + response.voucher_id, "_blank");
              } else if (result.isDenied) {
                window.open('print_voucher.php?type=credit&voucher_id=' + response.voucher_id, "_blank");
              } else {
                window.open('print_voucher.php?type=both&voucher_id=' + response.voucher_id, "_blank");
              }
            })
            // 

          } else if (result.isDenied) {
            location.reload();

          }
        })
      }
    }); //ajax call
  }); //main
  $("#voucher_expense_fm").on('submit', function (e) {
    e.preventDefault();
    var form = $('#voucher_expense_fm');
    $.ajax({
      type: 'POST',
      url: form.attr('action'),
      data: form.serialize(),
      dataType: 'json',
      beforeSend: function () {
        $('#voucher_expense_btn').prop("disabled", true);
      },
      success: function (response) {
        if (response.sts == "success") {
          $('#voucher_expense_fm').each(function () {
            this.reset();
          });
          $("#tableData").load(location.href + " #tableData");
        }
        $('#voucher_expense_btn').prop("disabled", false);
        //    sweeetalert(response.msg,response.sts,1500);
        Swal.fire({
          title: response.msg,
          showDenyButton: true,
          showCancelButton: true,
          confirmButtonText: `Print`,
          denyButtonText: `Add New`,
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Which type of do you Print?',
              showDenyButton: true,
              showCancelButton: true,
              confirmButtonText: `Debit`,
              denyButtonText: `Credit`,
              cancelButtonText: 'Both',
            }).then((result) => {
              if (result.isConfirmed) {
                window.open('print_voucher.php?type=debit&voucher_id=' + response.voucher_id, "_blank");
              } else if (result.isDenied) {
                window.open('print_voucher.php?type=credit&voucher_id=' + response.voucher_id, "_blank");
              } else {
                window.open('print_voucher.php?type=both&voucher_id=' + response.voucher_id, "_blank");
              }
            })

          } else if (result.isDenied) {
            location.reload();

          }
        })
      }
    }); //ajax call
  }); //main
  $("#voucher_single_fm").on('submit', function (e) {
    e.preventDefault();
    var form = $('#voucher_single_fm');
    $.ajax({
      type: 'POST',
      url: form.attr('action'),
      data: form.serialize(),
      dataType: 'json',
      beforeSend: function () {
        $('#voucher_single_btn').prop("disabled", true);
      },
      success: function (response) {
        if (response.sts == "success") {
          $('#voucher_single_fm').each(function () {
            this.reset();
          });
          $("#tableData").load(location.href + " #tableData");
        }
        $('#voucher_single_btn').prop("disabled", false);
        sweeetalert(response.msg, response.sts, 1500);
        Swal.fire({
          title: response.msg,
          showDenyButton: true,
          showCancelButton: true,
          confirmButtonText: `Print`,
          denyButtonText: `Add New`,
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            window.open('print_voucher.php?type=debit&voucher_id=' + response.voucher_id, "_blank");


          } else if (result.isDenied) {
            location.reload();

          }
        })
      }
    }); //ajax call
  }); //main
  $("#get_product_code").on('change', function () {
    var code = $('#get_product_code').val();
    var credit_sale_type = $('#credit_sale_type').val();
    var payment_type = $('#payment_type').val();

    $.ajax({
      type: 'POST',
      url: 'php_action/custom_action.php',
      data: {
        getPrice: code,
        type: "code",
        credit_sale_type: credit_sale_type,
        payment_type: payment_type
      },
      dataType: "json",
      success: function (response) {

        if (response.sts === "error") {
          Swal.fire({
            icon: 'error',
            title: 'Product Not Found',
            text: response.msg || 'The entered product code does not exist.',
            timer: 2000,
            showConfirmButton: false,
            didClose: () => {
              $('#get_product_code').val('').focus();
            }
          });

          return;
        }

        // ✅ Continue normal processing
        $("#get_product_name").val(""); // Reset first

        $('#get_product_name option').each(function () {
          if ($(this).data('code') == response.product_code) {
            $(this).prop('selected', true).change();
            return false;
          }
        });

        $("#instockQty").html("In Stock: " + response.qty);

        if (payment_type == "cash_in_hand" || payment_type == "credit_sale") {
          $("#get_product_price").val(response.current_rate);
          $("#get_product_quantity").attr("max", response.qty);
          $('#addProductPurchase').prop("disabled", response.qty <= 0);
        } else {
          $("#get_product_price").val(response.purchase_rate);
          $('#get_sale_price').val(response.current_rate);
        }
      }
    });
  });





}); /*--------------end of-------------------------------------------------------*/
function pending_bills(value) {

  $.ajax({
    url: 'php_action/custom_action.php',
    type: 'post',
    data: {
      pending_bills_detils: value
    },
    dataType: 'json',
    success: function (response) {
      $("#bill_customer_name").empty().val(response.client_name);
      $("#order_id").empty().val(response.order_id);
      $("#bill_grand_total").empty().val(response.grand_total);
      $("#bill_paid_ammount").empty().val(response.paid);
      $("#bill_remaining").empty().val(response.due);
      $("#bill_paid").attr('max', response.due);
      $("#bill_paid").empty().val(0);
    }
  });
}

function getCustomer_name(value) {
  $.ajax({
    url: 'php_action/custom_action.php',
    type: 'post',
    data: {
      getCustomer_name: value
    },
    dataType: 'text',
    success: function (response) {
      var data = response.trim();
      $("#sale_order_client_name").empty().val(data);
    }
  });
}





function loadProducts(id) {
  $.ajax({
    url: 'php_action/custom_action.php',
    type: 'post',
    data: {
      getProductPills: id
    },
    dataType: 'text',
    success: function (response) {
      var data = response.trim();
      $("#products_list").empty().html(data);
    }
  });
}

$("#get_product_name").on('change', function () {
  var code = $('#get_product_name :selected').val();

  // ✅ Skip if code is null, empty, or undefined
  if (!code) return;

  var payment_type = $('#payment_type').val();
  var credit_sale_type = $('#credit_sale_type').val();

  $.ajax({
    type: 'POST',
    url: 'php_action/custom_action.php',
    data: {
      getPrice: code,
      type: "product",
      credit_sale_type: credit_sale_type,
      payment_type: payment_type
    },
    dataType: "json",
    success: function (response) {
      if (response.sts === "error") {
        Swal.fire({
          icon: 'error',
          title: 'Product Not Found',
          text: response.msg || 'The entered product code does not exist.',
          timer: 2000,
          showConfirmButton: false,
          didClose: () => {
            $('#get_product_code').val('').focus();
          }
        });

        return;
      }

      $("#instockQty").html("In stock: " + response.qty);

      if (payment_type === "cash_in_hand" || payment_type === "credit_sale") {
        $("#barcode_product").val(response.product_code);
        $("#get_product_price").val(response.current_rate);
        $("#get_product_price_wholesale").val(response.wholesale_rate);
        $("#get_product_quantity").attr("max", response.qty);
        $('#addProductPurchase').prop("disabled", response.qty <= 0);
        $("#get_purchase_price").val(response.purchase_rate);
      } else {
        $("#get_product_code").val(response.product_code.toUpperCase());
        $("#get_product_price").val(response.purchase_rate);
        $("#get_purchase_price").val(response.purchase_rate);
        $('#get_sale_price').val(response.current_rate);

      }
    }
  });
});


$("#product_code").on('change', function () {
  //var code=  $('#get_product_code').val();
  var code = $('#product_code').val();
  if (/^[A-Za-z0-9\-]+$/.test(code)) {
    $.ajax({
      type: 'POST',
      url: 'php_action/custom_action.php',
      data: {
        get_products_code: code
      },
      dataType: "json",
      success: function (response) {
        if (response.sts == "error") {
          $('#add_product_btn').prop('disabled', true);

          //$('#product_code').focus();
          //  Swal.fire({
          //   position: 'center',
          //   icon: 'error',
          //   title:response.msg,
          //   showConfirmButton: true,

          // });
          Swal.fire({
            title: response.msg,
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: `Edit it`,
            denyButtonText: `Scan Again`,
          }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
              window.open('product.php?act=add&edit_product_id=' + response.product_id);


            } else if (result.isDenied) {
              $('#product_code').val('');

            }
          })
        } else {
          $('#add_product_btn').prop('disabled', false);
        }


      }

    });
  } else {
    Swal.fire({
      position: 'center',
      icon: 'error',
      title: 'Please Enter Only Alphabets and Number Without Space and Characters',
      showConfirmButton: true,

    });
  }


});
$("#full_payment_check").on('click', function () {
  var checked = $('#full_payment_check').is(':checked');
  var grand = $('#product_grand_total_amount').html();

  if (checked) {
    $('#paid_ammount').val(grand);
  } else {
    $('#paid_ammount').val(0);
  }
  $('#paid_ammount').trigger('keyup');

});
//function addProductPurchase() {


function getRandomInt(max) {
  return Math.floor(Math.random() * max);
}


$("#voucher_from_account,#voucher_to_account").on('change', function () {
  var from = $("#voucher_from_account :selected").val();
  var to = $("#voucher_to_account :selected").val();
  if (from == to) {
    sweeetalert("Accounts should not be same ", "error", 1500);
    $("#voucher_to_account ").prop('selectedIndex', 0);


  }

});
$("#addProductPurchase").on('click', function () {
  var total_price = 0;
  var profit_per_product = 0;
  var payment_type = $('#payment_type').val();

  var name = $('#get_product_name :selected').text();
  var price = $('.price-input').val();
  var purchaseprice = $('#get_purchase_price').val();
  var id = $('#get_product_name :selected').val();
  var code = ($('#get_product_code').val() || $('#barcode_product').val() || "").toUpperCase();

  var product_quantity = parseFloat($('#get_product_quantity').val());
  var pro_type = $('#add_pro_type').val();
  var max_qty = parseFloat($("#get_product_quantity").attr("max"));

  // If cash or credit purchase, assign high stock and get current rate
  if (payment_type == "cash_purchase" || payment_type == "credit_purchase") {
    max_qty = getRandomInt(99999999999);
    var current_rate = $('#get_sale_price').val();
  }

  // Validate quantity
  if (product_quantity <= 0 || isNaN(product_quantity)) {
    sweeetalert("Quantity must be greater than 0", "error", 1500);
    return;
  }

  if (id != '' && price != '' && max_qty >= product_quantity) {
    $('#add_pro_type').val('add');
    $('#get_product_name').val('').trigger('change');
    $('#get_product_code').val('').focus();
    $('#barcode_product').val('').focus();
    $('#get_product_price').val('');
    $('#get_product_price_wholesale').val('');
    $('#get_sale_price').val('');
    $('#get_product_quantity').val(0);
    $("#instockQty").html("In Stock: 0");

    if ($('#product_idN_' + id).length) {
      // Product already exists in table
      $(".product_ids").each(function () {
        var quantity = $(this).data('quantity'); // old quantity
        var val = $(this).val(); // product ID
        if (val == id) {
          // Calculate new quantity
          var Currentquantity = pro_type === "add" ?
            parseFloat(quantity) + product_quantity :
            product_quantity;

          total_price = parseFloat(price) * Currentquantity;
          profit_per_product = (parseFloat(price) - parseFloat(purchaseprice)) * Currentquantity;


          if (Currentquantity <= max_qty) {
            // Replace existing row with updated quantity & price
            $("#product_idN_" + id).replaceWith(`
              <tr id="product_idN_${id}">
                <input type="hidden" data-purchase="${purchaseprice}" data-price="${price}" data-quantity="${Currentquantity}" id="product_ids_${id}" class="product_ids" name="product_ids[]" value="${id}">
                <input type="hidden" id="product_quantites_${id}" name="product_quantites[]" value="${Currentquantity}">
                <input type="hidden" id="product_rate_${id}" name="product_rates[]" value="${price}">
                <input type="hidden" id="product_totalrate_${id}" name="product_totalrates[]" value="${total_price}">
                <input type="hidden" id="get_sale_price${id}" name="get_sale_price[]" value="${current_rate}">
                <td>${code || 'Not Assigned'}</td>
                <td>${name}</td>
                <td>${price}</td>
                <td>${Currentquantity}</td>
                ${!isNaN(profit_per_product) && profit_per_product !== "" ? `<td>${profit_per_product}</td>` : ""}
                <td>${total_price}</td>
                <td>
                  <button type="button" onclick="removeByid('#product_idN_${id}')" class="fa fa-trash text-danger"></button>
                  <button type="button" onclick="editByid(${id},'${code}','${price}','${Currentquantity}')" class="fa fa-edit text-success"></button>
                </td>
              </tr>
            `);
          } else {
            sweeetalert("Cannot Add Quantity more than stock", "error", 1500);
          }
        }
      });

    } else {
      // Product doesn't exist yet - add new row
      total_price = parseFloat(price) * product_quantity;
      profit_per_product = (parseFloat(price) - parseFloat(purchaseprice)) * product_quantity;



      $("#purchase_product_tb").append(`
        <tr id="product_idN_${id}">
          <input type="hidden" data-purchase="${purchaseprice}" data-price="${price}" data-quantity="${product_quantity}" id="product_ids_${id}" class="product_ids" name="product_ids[]" value="${id}">
          <input type="hidden" id="product_quantites_${id}" name="product_quantites[]" value="${product_quantity}">
          <input type="hidden" id="product_rate_${id}" name="product_rates[]" value="${price}">
          <input type="hidden" id="product_totalrate_${id}" name="product_totalrates[]" value="${total_price}">
          <input type="hidden" id="get_sale_price${id}" name="get_sale_price[]" value="${current_rate}">
          <td>${code || 'Not Assigned'}</td>
          <td>${name}</td>
          <td>${price}</td>
          <td>${product_quantity}</td>
          ${!isNaN(profit_per_product) && profit_per_product !== "" ? `<td>${profit_per_product}</td>` : ""}
          <td>${total_price}</td>
          <td>
            <button type="button" onclick="removeByid('#product_idN_${id}')" class="fa fa-trash text-danger"></button>
            <button type="button" onclick="editByid(${id},'${code}','${price}','${product_quantity}')" class="fa fa-edit text-success"></button>
          </td>
        </tr>
      `);

    }


    getOrderTotal();
  } else {
    if (max_qty < product_quantity) {
      sweeetalert("Cannot Add Quantity more than stock", "error", 1500);
    } else if (id == '') {
      sweeetalert("Select the product first", "error", 1500);
    }
  }
});


function removeByid(id) {

  $(id).remove();
  getOrderTotal();
}

function getOrderTotal() {
  var payment_type = $("#payment_type").val();
  var total_bill = 0;
  var grand_total = 0;
  var total_profit = 0;
  var total_return_amount = 0;
  var has_return = false;

  $(".product_ids").each(function (index) {
    var quantity = parseFloat($(this).data('quantity')) || 0;
    var selling_rate = parseFloat($(this).data('price')) || 0;
    var purchase_rate = parseFloat($(this).data('purchase')) || 0;

    var return_qty = parseFloat($("input[name='return_quantities[]']").eq(index).val()) || 0;

    if (return_qty > 0) {
      has_return = true;
    }

    var final_qty = quantity - return_qty;
    if (final_qty < 0) final_qty = 0;

    total_bill += selling_rate * final_qty;
    total_profit += (selling_rate - purchase_rate) * final_qty;

    total_return_amount += return_qty * selling_rate;
  });
  // Adjust profit for discount
  var discount = parseFloat($("#ordered_discount").val()) || 0;
  var freight = parseFloat($("#freight").val()) || 0;

  if (!(payment_type == "cash_in_hand" || payment_type == "credit_sale")) {
    freight = 0;
  }

  if (discount > total_profit) {
    alert("Discount cannot be greater than total profit.");
    $("#ordered_discount").val("").change(); // or use .val("0") if you prefer 0 instead of blank
    $("#ordered_discount").focus(); // keep field selected for correction
    discount = 0; // Ensure it's not used in calculations
  }

  total_profit -= discount;

  grand_total = (total_bill - discount) + freight;

  // Prevent NaN
  total_bill = isNaN(total_bill) ? 0 : total_bill;
  grand_total = isNaN(grand_total) ? 0 : grand_total;
  total_profit = isNaN(total_profit) ? 0 : total_profit;

  // 👇 Format display fields with comma
  $("#product_total_amount").html(total_bill.toLocaleString('en-US', {
    minimumFractionDigits: 2
  }));
  $("#product_grand_total_amount").html(grand_total.toLocaleString('en-US', {
    minimumFractionDigits: 2
  }));
  $("#total_profit_amount").html(total_profit.toLocaleString('en-US', {
    minimumFractionDigits: 2
  }));
  $("#total_profit").val(total_profit.toLocaleString('en-US', {
    minimumFractionDigits: 2
  }));

  // 👇 Keep input fields plain for calculations
  if (payment_type == "cash_in_hand" || payment_type == 'cash_purchase') {
    $("#paid_ammount").val(0);
    $("#paid_ammount").prop('required', true);
    if (payment_type == "cash_in_hand") {
      $("#full_payment_check").prop('checked', true);
    }
  } else {
    $("#paid_ammount").val('0');
    $("#paid_ammount").prop('required', false);
  }

  if (grand_total > 0) {
    $("input[name='payment_account']").prop('required', true);
  } else {
    $("input[name='payment_account']").prop('required', false);
  }

  // 👇 Set returnable amount in input field (not formatted)
  if (has_return) {
    $("#returnable_amount").val(total_return_amount.toFixed(2));
  } else {
    $("#returnable_amount").val("");
  }

  getRemaingAmount();
}

function getRemaingAmount() {
  var paid_ammount = parseFloat($('#paid_ammount').val()) || 0;
  var total_profit_amount = parseFloat($('#total_profit_amount').text().replace(/,/g, ''));

  // Remove commas before parsing
  var grand_total_text = $('#product_grand_total_amount').html().replace(/,/g, '');
  var product_grand_total_amount = parseFloat(grand_total_text) || 0;

  var total = product_grand_total_amount - paid_ammount;

  // Absolute value to avoid negative display
  $('#remaining_ammount').val(Math.abs(total).toFixed(2));
  $('#paid_ammount').attr('min', product_grand_total_amount);
  $('#ordered_discount').attr('max', total_profit_amount);
}

function editByid(id, code, price, qty) {
  $('.searchableSelect').val(id);

  $('#get_product_code').val(code);
  $('#get_product_quantity').val(qty);
  $('#add_pro_type').val('update');

  var effect = function () {
    return $('.searchableSelect').select2().trigger('change');
  };

  $.when(effect()).done(function () {
    setTimeout(function () {
      $('#get_product_price').val(price);
    }, 500);

  });

}

function getBalance(val, id) {

  setTimeout(function () {
    if (id == "customer_account_exp") {
      var value = $('#customer_account').val();
    } else {
      var value = val;
    }
    $.ajax({
      type: 'POST',
      url: 'php_action/custom_action.php',
      data: {
        getBalance: value
      },
      dataType: "text",
      success: function (msg) {
        var res = msg.trim();
        $("#" + id).html(res);

      }

    }); //ajax call }
  }, 1000);
}
// ---------------------------order gui---------------------------------------

$(document).ready(function () {
  $("#barcode_product").focus();
  $("#get_product_code").focus();

  $("#barcode_product").on('keydown', function (event) {
    if (event.key === "Enter" || event.keyCode === 13) {
      event.preventDefault();
      let barcode = $(this).val().trim();
      if (barcode !== "") {
        // alert("Scanned barcode: " + barcode);
        addbarcode_product(barcode, 'plus');
        $(this).val(''); // Clear the input

        // Focus back after short delay
        setTimeout(() => {
          $("#barcode_product").focus();
        }, 100);
      } else {
        alert("No barcode scanned!");
        setTimeout(() => {
          $("#barcode_product").focus();
        }, 100);
      }
    }
  });
});



function addbarcode_product(code, action_value) {
  var order_type = $("#ratetype").val();

  $.ajax({
    url: 'php_action/custom_action.php',
    type: 'post',
    data: {
      getProductDetailsBycode: code
    },
    dataType: 'json',
    success: function (res) {
      // Choose price based on order type
      let display_rate = order_type === "wholesale" ? parseFloat(res.f_days) : parseFloat(res.current_rate);

      if (res.quantity_instock > 0) {
        if ($('#product_idN_' + res.product_id).length) {
          $(".product_ids").each(function () {
            var quantity = $(this).data('quantity');
            var val = $(this).val();

            if (val == res.product_id) {
              var Currentquantity = parseFloat(quantity);

              if (action_value === "plus") {
                Currentquantity += 1;
              } else if (action_value === "minus") {
                if (Currentquantity > 1) {
                  Currentquantity -= 1;
                } else {
                  sweeetalert("Quantity cannot be less than 1", "warning", 1500);
                  return;
                }
              }

              if (Currentquantity > parseFloat(res.quantity_instock)) {
                sweeetalert("Only " + res.quantity_instock + " items in stock!", "error", 2000);
                return;
              }

              $("#product_idN_" + res.product_id).replaceWith(`
                <tr id="product_idN_${res.product_id}">
                  <input type="hidden"
                    data-price="${display_rate}"
                    data-purchase="${res.purchase_rate}"
                    data-quantity="${Currentquantity}"
                    id="product_ids_${res.product_id}"
                    class="product_ids"
                    name="product_ids[]"
                    value="${res.product_id}">
                  <input type="hidden" id="product_quantites_${res.product_id}" name="product_quantites[]" value="${Currentquantity}">
                  <input type="hidden" id="product_rates_${res.product_id}" name="product_rates[]" value="${display_rate}">
                  <td>${res.product_code.toUpperCase()}</td>
                  <td>${res.product_name.toUpperCase()} (<span class="text-success">${res.brand_name.toUpperCase()}</span>)</td>
                  <td>${display_rate}</td>
                  <td>${Currentquantity}</td>
                  <td>${(display_rate * Currentquantity - res.purchase_rate * Currentquantity).toFixed(2)}</td>
                  <td>${(display_rate * Currentquantity).toFixed(2)}</td>
                  <td>
                    <button type="button" onclick="addbarcode_product('${res.product_code}', 'plus')" class="btn btn-sm btn-success" title="Increase quantity">+ Add</button>
                    <button type="button" onclick="addbarcode_product('${res.product_code}', 'minus')" class="btn btn-sm btn-warning" title="Decrease quantity">− Remove</button>
                    <button type="button" onclick="removeByid('#product_idN_${res.product_id}')" class="btn btn-sm btn-danger" title="Remove product">🗑️ Delete</button>
                  </td>
                </tr>
              `);
            }
          });

          getOrderTotal();

        } else {
          // Append new row if it doesn’t exist
          $("#purchase_product_tb").append(`
            <tr id="product_idN_${res.product_id}">
              <input type="hidden"
                data-price="${display_rate}"
                data-purchase="${res.purchase_rate}"
                data-quantity="1"
                id="product_ids_${res.product_id}"
                class="product_ids"
                name="product_ids[]"
                value="${res.product_id}">
              <input type="hidden" id="product_quantites_${res.product_id}" name="product_quantites[]" value="1">
              <input type="hidden" id="product_rate_${res.product_id}" name="product_rates[]" value="${display_rate}">
              <input type="hidden" id="product_totalrate_${res.product_id}" name="product_totalrates[]" value="${display_rate}">
              <td>${res.product_code.toUpperCase()}</td>
              <td>${res.product_name.toUpperCase()} (<span class="text-success">${res.brand_name.toUpperCase()}</span>)</td>
              <td>${display_rate}</td>
              <td>1</td>
              <td>${(display_rate - res.purchase_rate).toFixed(2)}</td>
              <td>${display_rate}</td>
              <td>
                <button type="button" onclick="addbarcode_product('${res.product_code}', 'plus')" class="btn btn-sm btn-success" title="Increase quantity">+ Add</button>
                <button type="button" onclick="addbarcode_product('${res.product_code}', 'minus')" class="btn btn-sm btn-warning" title="Decrease quantity">− Remove</button>
                <button type="button" onclick="removeByid('#product_idN_${res.product_id}')" class="btn btn-sm btn-danger" title="Remove product">🗑️ Delete</button>
              </td>
            </tr>
          `);
          getOrderTotal();
        }
      } else {
        sweeetalert("This product is out of stock", "error", 1500);
      }
    }
  });
}





// ---------------------------order gui---------------------------------------
function addProductOrder(id, max = 100, action_value) {

  $.ajax({
    url: 'php_action/custom_action.php',
    type: 'post',
    data: {
      getProductDetails: id
    },
    dataType: 'json',
    success: function (res) {
      console.log(action_value);

      if ($('#product_idN_' + id).length) {



        var jsonObj = [];
        $(".product_ids").each(function (index) {

          var quantity = $(this).data('quantity');
          var val = $(this).val();

          if (val == id) {

            //$("#product_idN_"+id).remove();

            if (action_value == "plus") {
              var Currentquantity = 1 + parseFloat(quantity);
            }
            if (action_value == "minus") {

              var Currentquantity = parseFloat(quantity) - 1;
            }



            $("#product_idN_" + id).replaceWith('<tr id="product_idN_' + id + '">\
          <input type="hidden" data-price="' + res.current_rate + '" data-quantity="' + Currentquantity + '" id="product_ids_' + id + '" class="product_ids" name="product_ids[]" value="' + res.product_id + '">\
          <input type="hidden" id="product_quantites_' + id + '" name="product_quantites[]" value="' + Currentquantity + '">\
          <input type="hidden" id="product_rates_' + id + '" name="product_rates[]" value="' + res.current_rate + '">\
          <td>' + res.product_name + ' (<span class="text-success">' + res.brand_name + '</span>) </td>\
          <td>' + res.current_rate + ' </td>\
          <td>' + Currentquantity + ' </td>\
          <td>' + (res.current_rate * Currentquantity) + ' </td>\
          <td> <button type="button" onclick="addProductOrder(' + id + ',' + res.quantity + ',`plus`)" class="fa fa-plus text-success" href="#" ></button>\
            <button type="button" onclick="addProductOrder(' + id + ',' + res.quantity + ',`minus`)" class="fa fa-minus text-warning" href="#" ></button>\
            <button type="button" onclick="removeByid(`#product_idN_' + id + '`)" class="fa fa-trash text-danger" href="#" ></button>\
            </td>\
          </tr>');

          }
          getOrderTotal();

        });
      } else {

        $("#purchase_product_tb").append('<tr id="product_idN_' + id + '">\
                <input type="hidden" data-price="' + res.current_rate + '"  data-quantity="1" id="product_ids_' + id + '" class="product_ids" name="product_ids[]" value="' + id + '">\
                <input type="hidden" id="product_quantites_' + id + '" name="product_quantites[]" value="1">\
                <input type="hidden" id="product_rate_' + id + '" name="product_rates[]" value="' + res.current_rate + '">\
                <input type="hidden" id="product_totalrate_' + id + '" name="product_totalrates[]" value="' + res.current_rate + '">\
                <td>' + res.product_name + ' (<span class="text-success">' + res.brand_name + '</span>)</td>\
                 <td>' + res.current_rate + '</td>\
                 <td>1</td>\
                <td>' + res.current_rate + '</td>\
                <td>\
                  <button type="button" onclick="addProductOrder(' + id + ',' + res.quantity + ',`plus`)" class="fa fa-plus text-success" href="#" ></button>\
            <button type="button" onclick="addProductOrder(' + id + ',' + res.quantity + ',`minus`)" class="fa fa-minus text-warning" href="#" ></button>\
            <button type="button" onclick="removeByid(`#product_idN_' + id + '`)" class="fa fa-trash text-danger" href="#" ></button>\
            </td>\
                </tr>');

        getOrderTotal();
      }
      //console.log(jsonObj);
    }


  });
}

function readonlyIt(value, read_it_id) {
  if (value == '') {
    $('#' + read_it_id).prop("readonly", false);
  } else {
    $('#' + read_it_id).prop("readonly", true);
  }

}

$("#product_mm,#product_inch,#product_meter").on('keyup', function () {
  getTotal_price();

});
$("#generate_code").on('click', function () {

  $.ajax({
    type: 'POST',
    url: 'php_action/custom_action.php',
    data: {
      getBarcode: 'bar'
    },
    dataType: "json",
    success: function (res) {

      $("#product_code").val(res);
      $("#product_code").trigger('change');


    }

  }); //ajax call }

});

function getVoucherPrint(voucher_id) {
  Swal.fire({
    title: 'Which type of do you Print?',
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: `Debit`,
    denyButtonText: `Credit`,
    cancelButtonText: 'Both',
  }).then((result) => {
    if (result.isConfirmed) {
      window.open('print_voucher.php?type=debit&voucher_id=' + voucher_id, "_blank");
    } else if (result.isDenied) {
      window.open('print_voucher.php?type=credit&voucher_id=' + voucher_id, "_blank");
    } else {
      window.open('print_voucher.php?type=both&voucher_id=' + voucher_id, "_blank");
    }
  })
}

function setAmountPaid(id, paid) {

  Swal.fire({
    title: 'Did the Customer Paid All Amount?',
    showCancelButton: true,
    confirmButtonText: `Yes`,
    cancelButtonText: 'No',
  }).then((result) => {
    if (result.isConfirmed) {

      $.ajax({
        url: 'php_action/custom_action.php',
        type: 'post',
        data: {
          setAmountPaid: id,
          paid: paid
        },
        dataType: 'json',
        success: function (res) {
          sweeetalert(res.msg, res.sts, 1500);
          if (res.sts == "success") {
            //$("#view_orders_tb").load(location.href+" #view_orders_tb > *");
            location.reload();
          }
        }
      });
    } else {

    }
  })
}

function setCheckStatus(id) {
  Swal.fire({
    title: 'What is the Checks Current Status?',
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: `Passed`,
    denyButtonText: `Failed`,
    cancelButtonText: 'Cancel',
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: 'php_action/custom_action.php',
        type: 'post',
        data: {
          setCheckStatus: id,
          status: '1'
        },
        dataType: 'json',
        success: function (res) {
          sweeetalert(res.msg, res.sts, 1500);
          if (res.sts == "success") {
            $("#check_tb").load(location.href + " #check_tb > *");
            // location.reload();
          }
        }
      });
    } else if (result.isDenied) {
      $.ajax({
        url: 'php_action/custom_action.php',
        type: 'post',
        data: {
          setCheckStatus: id,
          status: '3'
        },
        dataType: 'json',
        success: function (res) {
          sweeetalert(res.msg, res.sts, 1500);
          if (res.sts == "success") {
            $("#check_tb").load(location.href + " #check_tb > *");
            //location.reload();
          }
        }
      });
    } else {}
  })

}

function addproductnow() {
  $("#addProductPurchase").click();
  $("#get_product_code").focus();

  // $("#get_product_name").load(location.href + " #get_product_name > *");


  $('#get_product_quantity').val(0);

}

function addproductnow1() {
  // alert('123');
  $("#get_product_price").focus().select();
}



function printOrder(orderId = null) {
  if (orderId) {

    $.ajax({
      url: 'print_sale.php',
      type: 'post',
      data: {
        id: orderId
      },

      dataType: 'text',
      success: function (response) {

        var mywindow = window.open('', 'Mart POS', 'height=400,width=600');
        mywindow.document.write('<html><head><title>Order Invoice</title>');
        mywindow.document.write('</head><body>');
        mywindow.document.write(response);
        mywindow.document.write('</body></html>');

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10

        mywindow.print();
        mywindow.close();

      } // /success function
    }); // /ajax function to fetch the printable order
  } // /if orderId
} // /print order function