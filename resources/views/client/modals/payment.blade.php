<!-- Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalTitle"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-4" id="sellerName"></div>
            <div class="col-md-4" id="sellerMobile"></div>
            <div class="col-md-4" id="sellerEmail"></div>
            <hr style="border: 1px solid black;">
            <div class="col-md-12">Your Order(s)</div>
            <div class="col-md-12" id="orderslist"></div>
            <hr style="border: 1px solid black;">
            <div class="col-md-12" id="total"></div>
            <hr style="border: 1px solid black;">
            <div class="col-md-12">
                <small>
                    Payments can be done using gcash or cod, exclusion of delivery fee. The delivery fee is P100.00 (one hundres pesos)
                    if you are paying via gcash, please add to note of your control number.
                </small>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <div id="paymentButton"></div>
      </div>
    </div>
  </div>
</div>