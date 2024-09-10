<!-- Email Update successfully message -->
@if(session('success'))
<div class="alert alert-success">
	<?= session('message') ?>
</div>
@endif
<!-- /Email Update successfully message -->

<!-- Email Not Update message -->
@if(!session('success'))
<div class="alert alert-danger">
	<?= session('message') ?>
</div>
@endif
<!-- /Email Not Update message -->