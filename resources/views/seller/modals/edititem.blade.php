<!-- Modal -->
<div class="modal fade" id="editThisModal" tabindex="-1" role="dialog" aria-labelledby="editThisTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editThisTitle"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-4">
                <label for="product">Product</label>
                <input type="text" class="form-control" id="product"/>
            </div>
            <div class="col-md-4">
                <label for="price">Price</label>
                <input type="text" class="form-control" id="price"/>
            </div>
            <div class="col-md-4">
                <label for="quantity">Quantity</label>
                <input type="text" class="form-control" id="quantity"/>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <div id="deleteBtn"></div>
        <div id="editBtn"></div>
      </div>
    </div>
  </div>
</div>