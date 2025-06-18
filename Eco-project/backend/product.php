<?php
// Product management functions
require_once 'config.php';

/**
 * Get all products
 * 
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array List of products
 */
function getAllProducts($limit = null, $offset = null) {
    try {
        $conn = getDbConnection();
        
        $sql = "SELECT * FROM products ORDER BY id DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
            
            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }
        
        $stmt = $conn->query($sql);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $products];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get product by ID
 * 
 * @param int $productId Product ID
 * @return array Product data
 */
function getProduct($productId) {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $product];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get products by category
 * 
 * @param string $category Category name
 * @return array List of products
 */
function getProductsByCategory($category) {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY id DESC");
        $stmt->execute([$category]);
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $products];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get products by subcategory
 * 
 * @param string $subcategory Subcategory name
 * @return array List of products
 */
function getProductsBySubcategory($subcategory) {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("SELECT * FROM products WHERE subcategory = ? ORDER BY id DESC");
        $stmt->execute([$subcategory]);
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $products];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Search products
 * 
 * @param string $query Search query
 * @return array List of products
 */
function searchProducts($query) {
    try {
        $conn = getDbConnection();
        
        $searchTerm = "%$query%";
        
        $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? OR category LIKE ? OR subcategory LIKE ? ORDER BY id DESC");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $products];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Add a new product (admin function)
 * 
 * @param string $name Product name
 * @param string $description Product description
 * @param float $price Product price
 * @param string $category Product category
 * @param string $subcategory Product subcategory
 * @param string $image Product image path
 * @param int $stock Product stock quantity
 * @return array Result with success status and message
 */
function addProduct($name, $description, $price, $category, $subcategory, $image, $stock) {
    // Validate input
    if (empty($name) || empty($price) || empty($category)) {
        return ['success' => false, 'message' => 'Name, price and category are required'];
    }
    
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, subcategory, image, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $category, $subcategory, $image, $stock]);
        
        $productId = $conn->lastInsertId();
        
        return [
            'success' => true, 
            'message' => 'Product added successfully',
            'product_id' => $productId
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update a product (admin function)
 * 
 * @param int $productId Product ID
 * @param string $name Product name
 * @param string $description Product description
 * @param float $price Product price
 * @param string $category Product category
 * @param string $subcategory Product subcategory
 * @param string $image Product image path
 * @param int $stock Product stock quantity
 * @return array Result with success status and message
 */
function updateProduct($productId, $name, $description, $price, $category, $subcategory, $image, $stock) {
    // Validate input
    if (empty($name) || empty($price) || empty($category)) {
        return ['success' => false, 'message' => 'Name, price and category are required'];
    }
    
    try {
        $conn = getDbConnection();
        
        // Check if product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        // Update product
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, category = ?, subcategory = ?, stock = ?";
        $params = [$name, $description, $price, $category, $subcategory, $stock];
        
        // Only update image if provided
        if (!empty($image)) {
            $sql .= ", image = ?";
            $params[] = $image;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $productId;
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return ['success' => true, 'message' => 'Product updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Delete a product (admin function)
 * 
 * @param int $productId Product ID
 * @return array Result with success status and message
 */
function deleteProduct($productId) {
    try {
        $conn = getDbConnection();
        
        // Check if product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        // Delete product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        return ['success' => true, 'message' => 'Product deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update product stock (admin function)
 * 
 * @param int $productId Product ID
 * @param int $stock New stock quantity
 * @return array Result with success status and message
 */
function updateProductStock($productId, $stock) {
    try {
        $conn = getDbConnection();
        
        // Check if product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        // Update stock
        $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->execute([$stock, $productId]);
        
        return ['success' => true, 'message' => 'Stock updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get all product categories
 * 
 * @return array List of categories
 */
function getAllCategories() {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return ['success' => true, 'data' => $categories];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get all product subcategories
 * 
 * @param string $category Optional category filter
 * @return array List of subcategories
 */
function getAllSubcategories($category = null) {
    try {
        $conn = getDbConnection();
        
        if ($category) {
            $stmt = $conn->prepare("SELECT DISTINCT subcategory FROM products WHERE category = ? ORDER BY subcategory");
            $stmt->execute([$category]);
        } else {
            $stmt = $conn->query("SELECT DISTINCT subcategory FROM products ORDER BY subcategory");
        }
        
        $subcategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return ['success' => true, 'data' => $subcategories];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get featured products
 * 
 * @param int $limit Number of products to return
 * @return array List of featured products
 */
function getFeaturedProducts($limit = 6) {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("SELECT * FROM products ORDER BY RAND() LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $products];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
?>
