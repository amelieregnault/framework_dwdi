<?php  /** @var array $product */ ?>

<h1>Edit Product</h1>

<form action="/products/<?= $product["id"] ?>/update" method="post">
    <?php require "form.php" ?>
</form>

</body>
</html>