@extends('layout.header')

@section('title', 'Seller Dashboard')

@section('content')
<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link active" data-toggle="tab" href="#myitems">My Items</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#orders">Pending Paid Orders</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#doneOrders">For Delivery</a>
  </li>
</ul>
<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane container active" id="myitems">
        <div class="tableDiv mt-5">
            <button class="btn btn-info mb-3" onclick="addItem()">Add</button>
            <table class="table table-striped" id="myItemsTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Edit</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="addItem mt-5">
            <div class="row">
                <div class="col-md-3 col-lg-3 col-sm-12">
                    <label for="categoriesAdd">Categories</label>
                    <select name="categoriesAdd" id="categoriesAdd" class="form-control"></select>
                </div>
                <div class="col-md-3 col-lg-3 col-sm-12">
                    <label for="productAdd">Product</label>
                    <input type="text" class="form-control form-control-sm" id="productAdd"/>
                </div>
                <div class="col-md-3 col-lg-3 col-sm-12">
                    <label for="quantityAdd">Quantity</label>
                    <input type="number" class="form-control form-control-sm" id="quantityAdd"/>
                </div>
                <div class="col-md-3 col-lg-3 col-sm-12">
                    <label for="priceAdd">Price</label>
                    <input type="number" class="form-control form-control-sm" id="priceAdd"/>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-info float-right pr-3 mt-5" id="addItem">Add</button>
                    <button class="btn btn-danger float-right pr-3 mt-5" id="cancelItem">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane container fade" id="orders">
        <table class="table table-sm table-striped" id="pendingOrdersTable" style="width: 100%;">
            <thead>
                <tr>
                    <th>Control Number</th>
                    <th>Products</th>
                    <th>Quantity</th>
                    <th>Total Payment</th>
                    <th>For Delivery</th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="tab-pane container fade" id="doneOrders">
        <table class="table table-striped" id="forDeliveryOrders" style="width: 100%;">
            <thead>
                <tr>
                    <th>Control Number</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@include('seller.modals.edititem')
@endsection

@section('scripts')
<script>
var myItemsTable, pendingOrdersTable, forDeliveryOrders;
$(document).ready(function(){
    fetchMyItems();
    fetchPendingPaidOrders();
    fetchForDeliveryOrders();
    $('.addItem').hide();

    $(document).on('click', '#cancelItem', function(){
        $('#productAdd').val('');
        $('#quantityAdd').val('');
        $('#priceAdd').val('');
        $('.addItem').hide();
        $('.tableDiv').show();
    });

    $(document).on('click', '#addItem', function(){
        var productAdd = $('#productAdd').val();
        var quantityAdd = $('#quantityAdd').val();
        var priceAdd = $('#priceAdd').val();
        var categoriesAdd = $('#categoriesAdd').val();
        $.ajax({
            url : "{{ url('addItem') }}",
            method : "POST",
            data : {
                "_token": "{{ csrf_token() }}",
                product : productAdd,
                quantity : quantityAdd,
                price : priceAdd,
                categories : categoriesAdd
            }
        }).done(function(response){
            if(response.response){
                toastr.success(response.message);
                $('.addItem').hide();
                $('.tableDiv').show();
                $('#productAdd').val('');
                $('#quantityAdd').val('');
                $('#priceAdd').val('');
                fetchMyItems();
            }else{
                toastr.error(response.message);
            }
        });
    });

});


function fetchForDeliveryOrders(){
    $.ajax({
        url : "{{ url('fetchForDeliveryOrders') }}",
        method : "GET"
    }).done(function(response){
        if(response.response){
            forDeliveryOrders = $('#forDeliveryOrders').DataTable().destroy();
            forDeliveryOrders = $('#forDeliveryOrders').DataTable({
                data: response.data,
                columns:[
                    { data: "controlNumber" }
                ]
            });
            $('.dataTables_length').css("display", "none");
        }
    });
}

function fetchPendingPaidOrders(){
    $.ajax({
        url : "{{ url('fetchPendingPaidOrders') }}",
        method : "GET"
    }).done(function(response){
        if(response.response){
            pendingOrdersTable = $('#pendingOrdersTable').DataTable().destroy();
            pendingOrdersTable = $('#pendingOrdersTable').DataTable({
                data: response.data,
                columns:[
                    { data: "controlNumber" },
                    { data: "orders" },
                    { data: "quantity" },
                    { data: "total" },
                    { 
                        data: "",
                        defaultContent:  "<button class='btn btn-info' onclick='forDelivery("+response.data[0].controlNumber+")'>For Delivery</button>"
                    }
                ]
            });
            $('.dataTables_length').css("display", "none");
        }
    });
}

function fetchMyItems(){
    $.ajax({
        url : "{{ url('fetchMyItems') }}",
        method : "get"
    }).done(function(response){
        if(response.response){
            myItemsTable = $('#myItemsTable').DataTable().destroy();
            myItemsTable = $('#myItemsTable').DataTable({
                data: response.data,
                columns:[
                    { data: "category" },
                    { data: "product" },
                    { 
                        data: "quantity",
                        render: function(data, type, row){
                            return "<label>"+data+" KG</label>";
                        }
                    },
                    { 
                        data: "price",
                        render: function(data, type, row){
                            return "<label>P"+data+".00</label>"
                        }
                    },
                    {
                        data: 'btnId',
                        render: function(data, type, row){
                            return "<button class='btn btn-outline-info' onclick='editThisItem("+data+")'>Edit</button>"
                        }
                    }
                ]
            });
            $('.dataTables_length').css("display", "none");
        }
    });
}

function addItem(){
    fetchCategories();
    $('.tableDiv').hide();
    $('.addItem').show();
}

function fetchCategories(){
    $('#categoriesAdd').empty();
    $.ajax({
        url : "{{ url('fetchCategories') }}",
        method : "GET"
    }).done(function(response){
        if(response.response){
            var option = "";
            $.each(response.data, function(i, v){
                var id = response.data[i].id;
                var name = response.data[i].category;
                option += "<option value='"+id+"'>"+name+"</option>";
            });
            $('#categoriesAdd').append(option);
            $('#categoriesAdd').select2();
        }
    });
}

function editThisItem(id){
    // console.log(id);
    $.ajax({
        url : "{{ url('editThisItem') }}",
        method: "POST",
        data : {
            "_token": "{{ csrf_token() }}",
            id: id
        }
    }).done(function(response){
        if(response.response){
            $('#editThisModal').modal('show');
            $('#product').val(response.data[0].product);
            $('#price').val(response.data[0].price);
            $('#quantity').val(response.data[0].quantity);
            $('#deleteBtn').html("<button class='btn btn-danger' onclick='deleteThis("+response.data[0].btnId+")'>Delete</button>");
            $('#editBtn').html("<button class='btn btn-info' onclick='editThis("+response.data[0].btnId+")'>Edit</button>");
        }
    });
}

function deleteThis(id){
    $.ajax({
        url : "{{ url('deleteThisItem') }}",
        method : "post",
        data : {
            "_token": "{{ csrf_token() }}",
            id: id
        }
    }).done(function(response){
        if(response.response){
            $('#editThisModal').modal('hide');
            fetchPendingPaidOrders();
            fetchMyItems();
        }
    });
}

function editThis(id){
    var product = $('#product').val();
    var price = $('#price').val();
    var quantity = $('#quantity').val();
    $.ajax({
        url : "{{ url('editThis') }}",
        method : "POST",
        data : {
            "_token": "{{ csrf_token() }}",
            id: id,
            product : product,
            price : price,
            quantity : quantity
        }
    }).done(function(response){
        if(response.response){
            $('#editThisModal').modal('hide');
            fetchPendingPaidOrders();
            fetchMyItems();
        }
    });
}

function forDelivery(id){
    $.ajax({
        url : "{{ url('forDelivery') }}",
        method : "POST",
        data : {
            "_token": "{{ csrf_token() }}",
            id: id
        }
    }).done(function(response){
        if(response.response){
            toastr.success(response.message);
            fetchPendingPaidOrders();
            fetchMyItems();
        }else{
            toastr.error(response.message);
        }
    });
}

</script>
@endsection