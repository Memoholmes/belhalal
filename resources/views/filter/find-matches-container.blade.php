<div class="alert alert-success">
    <?= __trn('__filterCount__ Match Found', '__filterCount__ Matches Found', $totalCount, ["__filterCount__" => $totalCount]) ?>
</div>
<!-- /Advance Filter Options -->
<div class="row row-cols-sm-1 row-cols-md-3 row-cols-lg-6 row-cols-xl-8" id="lwUserFilterContainer">
    @if(!__isEmpty($filterData))
    @include('filter.find-matches')
    @endif
</div>
@if($hasMorePages)
<div class="lw-load-more-container">
    <button type="button" class="btn btn-dark btn-block lw-ajax-link-action" id="lwLoadMoreButton" data-action="<?= $nextPageUrl ?>" data-event-callback="onLoadMoreFilterUsers" data-callback="loadMoreUsers"><?= __tr('Load More') ?></button>
</div>
@endif
<div id="lwLoadMoreResultMessage" style="display:none" class="col-sm-12 col-md-12 col-lg-12 alert alert-dark text-center bg-dark text-secondary border-0 mt-5"><?= __tr('Looks like you reached the end.') ?></div>