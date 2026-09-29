    <?php  /** @var int $total */ ?>

    <h1>Products (<?= $total ?>)</h1>

    <a href="/Products/new">New Product</a>

    <?php /** @var array $products */?>
    
    <?php foreach ($products as $product): ?>

        <h2>
            <a href="/products/<?= $product["id"] ?>/show">
                <?= htmlspecialchars($product["name"]) ?>
            </a>
        </h2>
            
    <?php endforeach ?>

</body>

</html>