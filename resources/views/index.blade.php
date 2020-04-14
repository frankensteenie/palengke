@extends('layout.header')

@section('title', 'Home Page')

@section('content')
<div class="col-md-4">
    <label for="categories">Categories</label>
    <select name="categories" id="categories" class="form-control"></select>
</div>
<div class="col-md-12 mt-5 row" id="items">
    
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        fetchCategories();

        $(document).on('change', '#categories', function(){
            var category_id = $(this).val();
            category_id === '0' ? toastr.error("Please select another category") : fetchData(category_id);
        });

    });

    function fetchCategories(){
        $('#categories').empty();
        $.ajax({
            url: "{{ url('api/getCategories') }}",
            method: "get"
        }).done(function(response){
            if(response.response){
                var option = "";
                option += "<option value='0' selected='selected'>SELECT CATEGORY</option>";
                $.each(response.data, function(i, v){
                    var id = response.data[i].id;
                    var name = response.data[i].name;
                    option += "<option value='"+id+"'>"+ name +"</option>";
                });
                $('#categories').append(option);
                var categories = $('#categories').select2();
            }
        });
    }

    function fetchData(id){
        $.ajax({
            url: "{{ url('fetchProducts') }}",
            method: "POST",
            data: {
                "_token": "{{ csrf_token() }}",
                id: id
            }
        }).done(function(response){
            $('#items').html(response.data);
        });
    }

    function addToCart(itemId, sellerId){
        $.ajax({
            url: "{{ url('addToCart') }}",
            method: "POST",
            data: {
                "_token": "{{ csrf_token() }}",
                itemId: itemId,
                sellerId: sellerId
            }
        }).done(function(response){
            if(response.response){
                toastr.success(response.message);
            }else{
                toastr.error(response.message);
            }
        });
    }

</script>
@endsection