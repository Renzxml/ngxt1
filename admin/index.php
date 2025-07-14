<?php include 'header.php';?>
  <link rel="stylesheet" href="../assets/admin-style.css"/>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Company Information</h2>
    <div>
      <a href="edit_details.php?id=<?= $company['cd_id'] ?>" class="btn btn-warning">Edit</a>
    </div>
  </div>

  <?php if ($company): ?>
  <?php $logoPath = '../assets/resources/' . $cd_logo; ?>
  <div class="card mb-3 shadow-lg">
    <div class="row g-0">
      <div class="col-md-4 text-center p-3">
        <?php if (!empty($cd_logo) && file_exists($logoPath)): ?>
          <img src="<?= htmlspecialchars($logoPath) ?>" alt="Company Logo" class="img-fluid" style="max-height: 200px;">
        <?php else: ?>
          <div class="text-muted">No Logo</div>
        <?php endif; ?>
      </div>
      <div class="col-md-8">
        <div class="card-body">
          <h4 class="card-title"><?= htmlspecialchars($cd_title) ?></h4>
          <h6 class="card-subtitle text-muted"><?= htmlspecialchars($cd_subtitle) ?>  <?= htmlspecialchars($cd_subtitle1) ?></h6>
          <p class="card-text mt-2"><strong></strong></p>
          <p class="card-text"><strong></strong> <?= nl2br(htmlspecialchars($cd_description)) ?></p>
        </div>
      </div>
    </div>
  </div>
  <?php else: ?>
    <div class="alert alert-warning">No company record found.</div>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
