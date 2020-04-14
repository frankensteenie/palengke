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
        test
    </div>
</div>

@endsection

@section('scripts')
<script>
var cartTable, onqueueorders;
$(document).ready(function(){
    fetchMyOrders();
    fetchQueuedOrders();
});
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
    console.log(id);
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