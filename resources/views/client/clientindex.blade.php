@extends('layout.header')

@section('title', 'Client Dashboard')

@section('content')
<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link active" data-toggle="tab" href="#orders">My Orders</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#onqueue">On Queue</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#fordelivery">For Delivery</a>
  </li>
</ul>
<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane container active" id="orders">
        <div class="col-md-12">
            <table class="table table-sm table-striped" id="cartTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Sold By</th>
                        <th>Date Ordered</th>
                        <th>Remove</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="tab-pane container fade" id="onqueue">
        <div class="col-md-12">
            <table class="table table-sm table-striped" id="onqueueorders" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Control Number</th>
                        <th>Orders</th>
                        <th>Total Price</th>
                        <th>Make A Payment</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="tab-pane container fade" id="fordelivery">
        <table class="table table-striped" id="forDeliveryOrders" style="width: 100%;">
            <thead>
                <tr>
                    <th>Control Number</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@include('client.modals.payment')
@endsection

@section('scripts')
<script>
var cartTable, onqueueorders, forDeliveryOrders;
$(document).ready(function(){
    fetchMyOrders();
    fetchQueuedOrders();
    fetchForDeliveryOrdersClient();
});
function fetchForDeliveryOrdersClient(){
    $.ajax({
        url : "{{ url('fetchForDeliveryOrdersClient') }}",
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
function fetchQueuedOrders(){
    $.ajax({
        url: "{{ url('fetchQueuedOrders') }}",
        method: "GET"
    }).done(function(response){
        onqueueorders = $('#onqueueorders').DataTable().destroy();
        onqueueorders = $('#onqueueorders').DataTable({
            data: response.data,
            columns:[
                { data: "controlNumber" },
                { data: "product" },
                { data: "price" },
                {
                    data: 'btnId',
                    render: function(data, type, row){
                        return "<button class='btn btn-outline-info' onclick='payment("+data+")'>PAY</button>"
                    }
                }
            ]
        });
        $('.dataTables_length').css("display", "none");
    });
}
function fetchMyOrders(){
    $.ajax({
        url: "{{ url('fetchMyOrders') }}",
        method: "GET"
    }).done(function(response){
        cartTable = $('#cartTable').DataTable().destroy();
        cartTable = $('#cartTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    text: "Checkout",
                    action: function(e, dt, node, config){
                        var data = cartTable.rows().data().toArray();
                        var orderId = [];
                        $.each(data, function(i, v){
                            orderId.push(
                                v.ordersId
                            );
                        });
                        addToGroup(orderId);
                    }
                }
            ],
            data: response.data,
            columns: [
                { data: "category"},
                { data: "product"},
                { data: "quantity"},
                { data: "price"},
                { data: "sellerInfo"},
                { data: "dateOrdered"},
                { 
                    data: 'ordersId',
                    render: function(data, type, row){
                        return "<button class='btn btn-outline-danger' onclick='deletethis("+data+")'>Remove</button>"
                    }
                }
            ]
        });
        $('.dataTables_length').css('display', 'none');
    });
}
function addToGroup(id){
    $.ajax({
        url: "{{ url('addToGroup') }}",
        method: "POST",
        data: {
            "_token": "{{ csrf_token() }}",
            ids: id
        }
    }).done(function(response){
        if(response.response){
            fetchMyOrders();
            fetchQueuedOrders();
        }
    });
}

function payment(id){
    // $('#paymentModal').modal('show');
    $.ajax({
        url: "{{ url('forPayment') }}",
        method: "post",
        data :{
            "_token": "{{ csrf_token() }}",
            id : id
        }
    }).done(function(response){
        if(response.response){
            var content = "";
            var total = 0;
            $('#paymentModalTitle').html("Control Number: " + response.controlNumber);
            $('#sellerName').html("Sold By:<br/>" + response.seller);
            $('#sellerMobile').html("GCash:<br/>" + response.mobileNumberSeller);
            $('#sellerEmail').html("Email:<br/>" + response.emailSeller);
            $.each(response.data, function(i, v){
                content += v.items + "<br/>"
                total += Number(v.total);
            });
            $('#orderslist').html(content);
            $('#total').html("Total: " + total);
            $('#paymentButton').html("<button class='btn btn-info' onclick='donePayment("+response.controlNumber+")'>Payment Done</button>");
            $('#paymentModal').modal('show');
        }
    });
}

function donePayment(id){
    $.ajax({
        url : "{{ url('paymentDone') }}",
        method : "POST",
        data : {
            "_token": "{{ csrf_token() }}",
            id : id
        }
    }).done(function(response){
        if(response.response){
            $('#paymentModal').modal('hide');
            toastr.success(response.message);
            fetchMyOrders();
            fetchQueuedOrders();
        }else{
            $('#paymentModal').modal('hide');
            toastr.error(response.message);
            fetchMyOrders();
            fetchQueuedOrders();
        }
    });
}

function deletethis(id){
    $.ajax({
        url : "{{ url('deleteThis') }}",
        method : "POST",
        data : {
            "_token": "{{ csrf_token() }}",
            id : id
        }
    }).done(function(response){
        if(response.response){
            fetchMyOrders();
            fetchQueuedOrders();
        }
    });
}

</script>
@endsection