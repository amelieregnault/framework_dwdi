<?php 

declare(strict_types=1);

namespace App\Repository;

use App\Models\Product;
use Framework\Repository;
use PDO;

class ProductRepository extends Repository
{

    // FIXME: Trouver un moyen pour que ce soit automatique
    protected string $associatedClass = Product::class;
    
    public function getTotal(): int{
        $sql = "SELECT COUNT(*) As total FROM product";

        $conn = $this->database->getConnection();
        $stmt = $conn->query($sql);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $row["total"];
    }


}