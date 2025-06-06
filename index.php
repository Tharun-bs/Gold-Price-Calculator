<?php
// DB connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'gold_calculator';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle AJAX requests for items or item details
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] == 'get_items' && isset($_GET['category_id'])) {
        $category_id = (int)$_GET['category_id'];
        $result = $conn->query("SELECT id, item_name FROM items WHERE category_id = $category_id");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        echo json_encode($items);
        exit;
    }

    if ($_GET['action'] == 'get_item_details' && isset($_GET['item_id'])) {
        $item_id = (int)$_GET['item_id'];
        $result = $conn->query("SELECT weight, wastage_percent, making_percent, tax_percent, image_path FROM items WHERE id = $item_id");
        echo json_encode($result->fetch_assoc());
        exit;
    }
}

// Fetch categories for page load
$categories_result = $conn->query("SELECT * FROM categories");
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Gold Price Calculator</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color:rgb(38, 185, 18);

    background-image: url('https://i.pinimg.com/736x/ff/9c/20/ff9c204f62b65141a988cde3c7b1484f.jpg');
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-position: center;
}

        
        h2 {
            text-align: center;
            color: #FFD700;
            margin-bottom: 30px;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .selection-row {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }
        
        .selection-row label {
            min-width: 100px;
            font-weight: bold;
        }
        
        .selection-row select {
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .selection-row img {
            max-width: 80px;
            max-height: 80px;
            border-radius: 5px;
        }
        
        .calculator-section {
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .calc-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .calc-row label {
            flex: 1;
            font-weight: bold;
            color: #333;
        }
        
        .calc-row input {
            width: 100px;
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 5px;
            text-align: center;
            margin: 0 10px;
        }
        
        .amount-display {
            min-width: 120px;
            padding: 8px 12px;
            background-color: #e9ecef;
            border: 2px solid #ced4da;
            border-radius: 5px;
            text-align: right;
            font-weight: bold;
            color: #495057;
        }
        
        .total-row {
            background-color: #d4edda;
            border: 2px solid #c3e6cb;
            font-size: 18px;
            font-weight: bold;
        }
        
        .total-row .amount-display {
            background-color: #c3e6cb;
            color: #155724;
            font-size: 18px;
        }
        
        .calculate-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .calculate-btn:hover:not(:disabled) {
            background-color: #0056b3;
        }
        
        .calculate-btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <h2>Gold Price Calculator</h2>

    <div class="form-container">
        <form id="goldForm" onsubmit="return false;">
            <div class="selection-row">
                <label for="category">Category:</label>
                <select id="category">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <img id="category-img" src="" alt="Category Image" style="display:none;" />
            </div>

            <div class="selection-row">
                <label for="item">Item:</label>
                <select id="item" disabled>
                    <option value="">Select Item</option>
                </select>
                <img id="item-img" src="" alt="Item Image" style="display:none;" />
            </div>

            <div class="calculator-section">
                <div class="calc-row">
                    <label>Gold Rate:</label>
                    <input type="number" id="goldRate" readonly placeholder="₹/g" />
                    <div class="amount-display" id="goldRateAmt">₹0.00</div>
                </div>

                <div class="calc-row">
                    <label>Gold Weight:</label>
                    <input type="number" id="weight" readonly placeholder="grams" />
                    <div class="amount-display" id="goldWtAmt">₹0.00</div>
                </div>

                <div class="calc-row">
                    <label>Making Charges %:</label>
                    <input type="number" id="making" readonly placeholder="%" />
                    <div class="amount-display" id="makingChargeAmt">₹0.00</div>
                </div>

                <div class="calc-row">
                    <label>Wastage %:</label>
                    <input type="number" id="wastage" readonly placeholder="%" />
                    <div class="amount-display" id="wastageAmt">₹0.00</div>
                </div>

                <div class="calc-row">
                    <label>Tax %:</label>
                    <input type="number" id="tax" readonly placeholder="%" />
                    <div class="amount-display" id="taxAmt">₹0.00</div>
                </div>

                <div class="calc-row total-row">
                    <label>Total Price:</label>
                    <input type="text" value="" readonly style="border: none; background: transparent; font-weight: bold;" />
                    <div class="amount-display" id="totalAmt">₹0.00</div>
                </div>
            </div>

            <button type="button" id="calculateBtn" class="calculate-btn" disabled>Calculate Price</button>
        </form>
    </div>

<script>
const categories = <?php echo json_encode($categories); ?>;

document.addEventListener('DOMContentLoaded', () => {
    const categorySelect = document.getElementById('category');
    const itemSelect = document.getElementById('item');
    const goldRateInput = document.getElementById('goldRate');
    const weightInput = document.getElementById('weight');
    const wastageInput = document.getElementById('wastage');
    const makingInput = document.getElementById('making');
    const taxInput = document.getElementById('tax');
    const categoryImg = document.getElementById('category-img');
    const itemImg = document.getElementById('item-img');
    const calculateBtn = document.getElementById('calculateBtn');
    
    // Amount display elements
    const goldRateAmt = document.getElementById('goldRateAmt');
    const goldWtAmt = document.getElementById('goldWtAmt');
    const makingChargeAmt = document.getElementById('makingChargeAmt');
    const wastageAmt = document.getElementById('wastageAmt');
    const taxAmt = document.getElementById('taxAmt');
    const totalAmt = document.getElementById('totalAmt');

    function resetItemDetails() {
        goldRateInput.value = '';
        weightInput.value = '';
        wastageInput.value = '';
        makingInput.value = '';
        taxInput.value = '';
        itemImg.style.display = 'none';
        itemImg.src = '';
        calculateBtn.disabled = true;
        
        // Reset all amount displays
        goldRateAmt.textContent = '₹0.00';
        goldWtAmt.textContent = '₹0.00';
        makingChargeAmt.textContent = '₹0.00';
        wastageAmt.textContent = '₹0.00';
        taxAmt.textContent = '₹0.00';
        totalAmt.textContent = '₹0.00';
    }

    categorySelect.addEventListener('change', () => {
        const categoryId = categorySelect.value;
        itemSelect.innerHTML = `<option value="">Select Item</option>`;
        itemSelect.disabled = true;
        resetItemDetails();

        if (!categoryId) {
            categoryImg.style.display = 'none';
            categoryImg.src = '';
            return;
        }

        // Show category image
        const catObj = categories.find(c => c.id == categoryId);
        if (catObj && catObj.image_path) {
            categoryImg.src = catObj.image_path;
            categoryImg.style.display = 'inline';
        } else {
            categoryImg.style.display = 'none';
            categoryImg.src = '';
        }

        fetch(`index.php?action=get_items&category_id=${categoryId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert('Error loading items: ' + data.error);
                    return;
                }
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.item_name;
                    itemSelect.appendChild(option);
                });
                itemSelect.disabled = false;
            })
            .catch(() => alert('Failed to load items for the selected category.'));
    });

    itemSelect.addEventListener('change', () => {
        const itemId = itemSelect.value;
        resetItemDetails();

        if (!itemId) return;

        fetch(`index.php?action=get_item_details&item_id=${itemId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert('Error loading item details: ' + data.error);
                    return;
                }
                weightInput.value = data.weight ?? '';
                wastageInput.value = data.wastage_percent ?? '';
                makingInput.value = data.making_percent ?? '';
                taxInput.value = data.tax_percent ?? '';

                if (data.image_path) {
                    itemImg.src = data.image_path;
                    itemImg.style.display = 'inline';
                } else {
                    itemImg.style.display = 'none';
                    itemImg.src = '';
                }

                calculateBtn.disabled = false;
            })
            .catch(() => alert('Failed to load item details.'));
    });

    calculateBtn.addEventListener('click', () => {
        const weight = parseFloat(weightInput.value);
        const wastage = parseFloat(wastageInput.value);
        const making = parseFloat(makingInput.value);
        const tax = parseFloat(taxInput.value);

        if ([weight, wastage, making, tax].some(v => isNaN(v))) {
            alert('Invalid item data. Please select a valid item.');
            return;
        }

        fetch('get_gold_data.php')
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert('Gold rate error: ' + data.error);
                    return;
                }

                const rate = parseFloat(data.rate);
                if (isNaN(rate) || rate <= 0) {
                    alert('Invalid gold rate from API.');
                    return;
                }

                // Update gold rate display
                goldRateInput.value = rate.toFixed(2);
                goldRateAmt.textContent = `₹${rate.toFixed(2)}`;

                // Calculate amounts
                const basePrice = weight * rate;
                const wastageCost = basePrice * (wastage / 100);
                const makingCost = basePrice * (making / 100);
                const subtotal = basePrice + wastageCost + makingCost;
                const taxCost = subtotal * (tax / 100);
                const total = subtotal + taxCost;

                // Update all amount displays
                goldWtAmt.textContent = `₹${basePrice.toFixed(2)}`;
                makingChargeAmt.textContent = `₹${makingCost.toFixed(2)}`;
                wastageAmt.textContent = `₹${wastageCost.toFixed(2)}`;
                taxAmt.textContent = `₹${taxCost.toFixed(2)}`;
                totalAmt.textContent = `₹${total.toFixed(2)}`;
            })
            .catch(() => alert('Failed to fetch gold rate.'));
    });
});
</script>
</body>
</html>